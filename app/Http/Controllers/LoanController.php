<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Organization;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    /**
     * Busca mutuários por identidade militar (AJAX).
     */
    public function searchBorrower(Request $request)
    {
        $term = $request->input('q', '');

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $users = User::with('rank')
            ->where('is_active', true)
            ->where(function ($q) use ($term) {
                $q->where('identity_number', 'ilike', "%{$term}%")
                  ->orWhere('war_name', 'ilike', "%{$term}%");
            })
            ->limit(10)
            ->get()
            ->map(fn ($u) => [
                'id'    => $u->id,
                'label' => $u->getDisplayName() . ' (' . $u->identity_number . ')',
            ]);

        return response()->json($users);
    }

    /**
     * Listagem de cautelas (empréstimos) com filtros.
     */
    public function index(Request $request)
    {
        $query = Loan::with(['borrower', 'borrowerOrganization', 'loanedBy']);

        // Busca por número da cautela ou mutuário
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('loan_number', 'ilike', "%{$search}%")
                  ->orWhere('borrower_section', 'ilike', "%{$search}%")
                  ->orWhereHas('borrower', function ($bq) use ($search) {
                      $bq->where('war_name', 'ilike', "%{$search}%")
                         ->orWhere('identity_number', 'ilike', "%{$search}%");
                  });
            });
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $loans    = $query->latest('loan_date')->paginate(15)->appends($request->query());
        $statuses = Loan::STATUSES;

        return view('loans.index', compact('loans', 'statuses'));
    }

    /**
     * Formulário de criação de nova cautela.
     */
    public function create()
    {
        $organizations = Organization::orderBy('abbreviation')->get();
        $products      = Product::with(['stockItems' => function ($q) {
            $q->available()
              ->where('quantity', '>', 0)
              ->where(function ($sq) {
                  // Excluir itens vencidos
                  $sq->whereNull('expiration_date')
                     ->orWhere('expiration_date', '>', now());
              });
        }])->orderBy('name')->get();

        return view('loans.create', compact('organizations', 'products'));
    }

    /**
     * Salva nova cautela (empréstimo).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'borrower_type'            => 'required|in:individual,section',
            'borrower_user_id'         => 'required_if:borrower_type,individual|nullable|exists:users,id',
            'borrower_section'         => 'required_if:borrower_type,section|nullable|string|max:150',
            'borrower_organization_id' => 'nullable|exists:organizations,id',
            'expected_return_date'     => 'nullable|date|after_or_equal:today',
            'notes'                    => 'nullable|string|max:1000',
            // Itens: array de {stock_item_id, quantity, condition_out}
            'items'                    => 'required|array|min:1',
            'items.*.stock_item_id'    => 'required|exists:stock_items,id',
            'items.*.quantity'         => 'required|integer|min:1',
            'items.*.condition_out'    => 'required|in:' . implode(',', array_keys(LoanItem::CONDITIONS)),
        ]);

        try {
            $loan = DB::transaction(function () use ($validated) {
                // Criar a cautela
                $loan = Loan::create([
                    'loan_number'              => Loan::generateLoanNumber(),
                    'borrower_type'            => $validated['borrower_type'],
                    'borrower_user_id'         => $validated['borrower_user_id'] ?? null,
                    'borrower_section'         => $validated['borrower_section'] ?? null,
                    'borrower_organization_id' => $validated['borrower_organization_id'] ?? null,
                    'loaned_by'                => Auth::id(),
                    'loan_date'                => now(),
                    'expected_return_date'     => $validated['expected_return_date'] ?? null,
                    'status'                   => 'active',
                    'notes'                    => $validated['notes'] ?? null,
                ]);

                // Criar itens e atualizar estoque
                foreach ($validated['items'] as $itemData) {
                    $stockItem = StockItem::findOrFail($itemData['stock_item_id']);

                    // Verificar disponibilidade
                    if (!$stockItem->isAvailable() || $stockItem->quantity < $itemData['quantity']) {
                        throw new \Exception("Item \"{$stockItem->product->name}\" sem estoque suficiente.");
                    }

                    // Impedir empréstimo de itens vencidos
                    if ($stockItem->isExpired()) {
                        throw new \Exception("Item \"{$stockItem->product->name}\" está com validade vencida e não pode ser emprestado.");
                    }

                    // Criar item da cautela
                    LoanItem::create([
                        'loan_id'       => $loan->id,
                        'stock_item_id' => $itemData['stock_item_id'],
                        'quantity'      => $itemData['quantity'],
                        'returned_quantity' => 0,
                        'condition_out' => $itemData['condition_out'],
                    ]);

                    // Atualizar status do item de estoque
                    $newQty = $stockItem->quantity - $itemData['quantity'];
                    $stockItem->update([
                        'quantity' => $newQty,
                        'status'   => $newQty <= 0 ? 'loaned' : 'available',
                    ]);

                    // Registrar movimentação
                    StockMovement::record(
                        stockItemId: $stockItem->id,
                        type: 'loan',
                        quantity: -$itemData['quantity'],
                        performedBy: Auth::id(),
                        referenceType: 'loan',
                        referenceId: $loan->id,
                        notes: "Empréstimo {$loan->loan_number}",
                    );
                }

                return $loan;
            });

            return redirect()
                ->route('loans.show', $loan)
                ->with('success', "Cautela {$loan->loan_number} registrada com sucesso.");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage() ?: 'Erro ao registrar cautela.');
        }
    }

    /**
     * Exibe detalhes de uma cautela.
     */
    public function show(Loan $loan)
    {
        $loan->load([
            'borrower.rank',
            'borrowerOrganization',
            'loanedBy.rank',
            'receivedBy.rank',
            'items.stockItem.product',
        ]);

        return view('loans.show', compact('loan'));
    }

    /**
     * Formulário de devolução (total ou parcial).
     */
    public function returnForm(Loan $loan)
    {
        // Cautela já totalmente devolvida
        if ($loan->status === 'returned') {
            return redirect()
                ->route('loans.show', $loan)
                ->with('info', 'Esta cautela já foi totalmente devolvida.');
        }

        $loan->load(['items.stockItem.product', 'borrower.rank', 'borrowerOrganization']);

        // Apenas itens com quantidade pendente
        $pendingItems = $loan->items->filter(function ($item) {
            return $item->getPendingQuantity() > 0;
        });

        if ($pendingItems->isEmpty()) {
            return redirect()
                ->route('loans.show', $loan)
                ->with('info', 'Todos os itens desta cautela já foram devolvidos.');
        }

        return view('loans.return', compact('loan', 'pendingItems'));
    }

    /**
     * Processa devolução de itens.
     */
    public function processReturn(Request $request, Loan $loan)
    {
        // Impedir devolução de cautela já devolvida
        if ($loan->status === 'returned') {
            return redirect()
                ->route('loans.show', $loan)
                ->with('error', 'Esta cautela já foi totalmente devolvida.');
        }

        $validated = $request->validate([
            'returns'                => 'required|array|min:1',
            'returns.*.loan_item_id' => 'required|exists:loan_items,id',
            'returns.*.quantity'     => 'required|integer|min:0',
            'returns.*.condition_in' => 'nullable|in:' . implode(',', array_keys(LoanItem::CONDITIONS)),
            'notes'                  => 'nullable|string|max:1000',
        ]);

        try {
            DB::transaction(function () use ($validated, $loan) {
                $allReturned = true;

                foreach ($validated['returns'] as $returnData) {
                    if ($returnData['quantity'] <= 0) {
                        continue;
                    }

                    $loanItem = LoanItem::findOrFail($returnData['loan_item_id']);

                    // Verificar se pertence a esta cautela
                    if ($loanItem->loan_id !== $loan->id) {
                        throw new \Exception('Item não pertence a esta cautela.');
                    }

                    // Verificar se a quantidade é válida
                    $pending = $loanItem->getPendingQuantity();
                    if ($returnData['quantity'] > $pending) {
                        throw new \Exception("Quantidade de devolução excede o pendente para \"{$loanItem->stockItem->product->name}\".");
                    }

                    // Atualizar item da cautela
                    $loanItem->update([
                        'returned_quantity' => $loanItem->returned_quantity + $returnData['quantity'],
                        'condition_in'      => $returnData['condition_in'] ?? null,
                    ]);

                    // Devolver ao estoque
                    $stockItem = $loanItem->stockItem;
                    $stockItem->update([
                        'quantity' => $stockItem->quantity + $returnData['quantity'],
                        'status'   => 'available',
                    ]);

                    // Registrar movimentação
                    StockMovement::record(
                        stockItemId: $stockItem->id,
                        type: 'return',
                        quantity: $returnData['quantity'],
                        performedBy: Auth::id(),
                        referenceType: 'loan',
                        referenceId: $loan->id,
                        notes: "Devolução da cautela {$loan->loan_number}",
                    );

                    // Verificar se ainda há pendentes
                    if ($loanItem->fresh()->getPendingQuantity() > 0) {
                        $allReturned = false;
                    }
                }

                // Verificar se TODOS os itens da cautela foram devolvidos
                $loan->refresh();
                $fullyReturned = $loan->items->every(fn ($item) => $item->isFullyReturned());

                $loan->update([
                    'status'             => $fullyReturned ? 'returned' : 'partial',
                    'actual_return_date' => $fullyReturned ? now() : null,
                    'received_by'        => Auth::id(),
                    'notes'              => $loan->notes
                        ? $loan->notes . "\n[Devolução] " . ($validated['notes'] ?? '')
                        : ($validated['notes'] ?? null),
                ]);
            });

            return redirect()
                ->route('loans.show', $loan)
                ->with('success', 'Devolução registrada com sucesso.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage() ?: 'Erro ao processar devolução.');
        }
    }

    /**
     * Gera PDF da cautela (documento de empréstimo).
     */
    public function cautelaPdf(Loan $loan)
    {
        $loan->load([
            'borrower.rank',
            'borrowerOrganization',
            'loanedBy.rank',
            'items.stockItem.product',
        ]);

        $pdf = Pdf::loadView('loans.pdf.cautela', compact('loan'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("cautela-{$loan->loan_number}.pdf");
    }

    /**
     * Gera PDF do recibo de devolução.
     */
    public function returnReceiptPdf(Loan $loan)
    {
        $loan->load([
            'borrower.rank',
            'borrowerOrganization',
            'loanedBy.rank',
            'receivedBy.rank',
            'items.stockItem.product',
        ]);

        $pdf = Pdf::loadView('loans.pdf.return-receipt', compact('loan'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("devolucao-{$loan->loan_number}.pdf");
    }
}
