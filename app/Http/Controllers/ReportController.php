<?php

namespace App\Http\Controllers;

use App\Exports\ExpirationAlertExport;
use App\Exports\LoanReportExport;
use App\Exports\MovementReportExport;
use App\Exports\StockReportExport;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockMovement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Painel de relatórios disponíveis.
     */
    public function index()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Acesso restrito ao administrador.');
        }

        return view('admin.reports.index');
    }

    /**
     * Gera relatório conforme tipo solicitado.
     */
    public function generate(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Acesso restrito ao administrador.');
        }

        $validated = $request->validate([
            'report_type' => 'required|in:stock_summary,loans_active,loans_history,movements,low_stock,expiring',
            'format'      => 'required|in:pdf,screen,excel',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
        ]);

        $type     = $validated['report_type'];
        $format   = $validated['format'];
        $dateFrom = $validated['date_from'] ?? null;
        $dateTo   = $validated['date_to'] ?? null;

        $data = match ($type) {
            'stock_summary' => $this->stockSummaryData(),
            'loans_active'  => $this->activeLoansData(),
            'loans_history' => $this->loansHistoryData($dateFrom, $dateTo),
            'movements'     => $this->movementsData($dateFrom, $dateTo),
            'low_stock'     => $this->lowStockData(),
            'expiring'      => $this->expiringData(),
        };

        $data['reportTitle'] = $this->getReportTitle($type);
        $data['dateFrom']    = $dateFrom;
        $data['dateTo']      = $dateTo;
        $data['generatedAt'] = now()->format('d/m/Y H:i');
        $data['generatedBy'] = Auth::user()->getDisplayName();

        $viewName = "admin.reports.types.{$type}";

        // ─── Excel ───────────────────────────────────────────────
        if ($format === 'excel') {
            $filename = "relatorio-{$type}-" . now()->format('Ymd') . '.xlsx';

            $export = match ($type) {
                'stock_summary' => new StockReportExport(),
                'loans_active'  => new LoanReportExport('active'),
                'loans_history' => new LoanReportExport('all', $dateFrom, $dateTo),
                'movements'     => new MovementReportExport($dateFrom, $dateTo),
                'low_stock'     => new StockReportExport(), // filtered in export
                'expiring'      => new ExpirationAlertExport(),
            };

            return Excel::download($export, $filename);
        }

        // ─── PDF ─────────────────────────────────────────────────
        if ($format === 'pdf') {
            $pdfView = "admin.reports.pdf.{$type}";
            $pdf = Pdf::loadView($pdfView, $data);
            $pdf->setPaper('A4', 'landscape');

            return $pdf->download("relatorio-{$type}-" . now()->format('Ymd') . '.pdf');
        }

        // Exibir na tela
        return view($viewName, $data);
    }

    // ─── Métodos privados de dados ───────────────────────────────

    private function stockSummaryData(): array
    {
        $products = Product::with(['category', 'stockItems'])
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                return [
                    'name'           => $product->name,
                    'category'       => $product->category->name ?? '—',
                    'unit'           => $product->unit,
                    'available'      => $product->getAvailableStock(),
                    'loaned'         => $product->getLoanedStock(),
                    'minimum'        => $product->minimum_stock,
                    'below_minimum'  => $product->isBelowMinimum(),
                ];
            });

        $totalAvailable = StockItem::available()->sum('quantity');
        $totalLoaned    = StockItem::loaned()->sum('quantity');

        return compact('products', 'totalAvailable', 'totalLoaned');
    }

    private function activeLoansData(): array
    {
        $loans = Loan::with(['borrower.rank', 'borrowerOrganization', 'loanedBy.rank', 'items.stockItem.product'])
            ->active()
            ->orderBy('loan_date')
            ->get();

        return compact('loans');
    }

    private function loansHistoryData(?string $dateFrom, ?string $dateTo): array
    {
        $query = Loan::with(['borrower.rank', 'borrowerOrganization', 'loanedBy.rank']);

        if ($dateFrom && $dateTo) {
            $query->forDateRange($dateFrom, $dateTo);
        }

        $loans = $query->latest('loan_date')->get();

        return compact('loans');
    }

    private function movementsData(?string $dateFrom, ?string $dateTo): array
    {
        $query = StockMovement::with(['stockItem.product', 'performedBy']);

        if ($dateFrom && $dateTo) {
            $query->forDateRange($dateFrom, $dateTo);
        }

        $movements = $query->latest()->get();

        return compact('movements');
    }

    private function lowStockData(): array
    {
        $products = Product::with('category')
            ->get()
            ->filter->isBelowMinimum()
            ->values();

        return compact('products');
    }

    private function expiringData(): array
    {
        $items = StockItem::with('product.category')
            ->expiringSoon(60)
            ->orderBy('expiration_date')
            ->get();

        return compact('items');
    }

    private function getReportTitle(string $type): string
    {
        return match ($type) {
            'stock_summary' => 'Resumo do Estoque',
            'loans_active'  => 'Cautelas Ativas',
            'loans_history' => 'Histórico de Cautelas',
            'movements'     => 'Movimentações de Estoque',
            'low_stock'     => 'Produtos com Estoque Baixo',
            'expiring'      => 'Itens Próximos da Validade',
        };
    }
}
