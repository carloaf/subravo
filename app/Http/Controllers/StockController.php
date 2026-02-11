<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StockController extends Controller
{
    /**
     * Listagem do estoque com filtros.
     */
    public function index(Request $request)
    {
        $query = StockItem::with('product.category');

        // Busca por produto ou lote
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('batch', 'ilike', "%{$search}%")
                  ->orWhere('serial_number', 'ilike', "%{$search}%")
                  ->orWhere('location', 'ilike', "%{$search}%")
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('name', 'ilike', "%{$search}%");
                  });
            });
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por produto
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $items    = $query->orderByDesc('created_at')->paginate(20)->appends($request->query());
        $products = Product::orderBy('name')->get();
        $statuses = StockItem::STATUSES;

        return view('stock.index', compact('items', 'products', 'statuses'));
    }

    /**
     * Exibe detalhes de um item de estoque.
     */
    public function show(StockItem $stockItem)
    {
        $stockItem->load(['product.category', 'movements' => function ($q) {
            $q->with('performedBy')->latest()->take(20);
        }]);

        return view('stock.show', compact('stockItem'));
    }

    /**
     * Formulário de entrada de material no estoque.
     */
    public function entry()
    {
        $products = Product::with('category')->orderBy('name')->get();

        return view('stock.entry', compact('products'));
    }

    /**
     * Processa entrada de material no estoque.
     */
    public function storeEntry(Request $request)
    {
        $validated = $request->validate([
            'product_id'         => 'required|exists:products,id',
            'quantity'           => 'required|integer|min:1',
            'batch'              => 'nullable|string|max:100',
            'serial_number'      => 'nullable|string|max:100',
            'expiration_date'    => 'nullable|date|after:today',
            'siscofis_entry_date'=> 'nullable|date',
            'location'           => 'nullable|string|max:100',
            'subunit'            => 'nullable|string|max:100',
            'notes'              => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($validated, $request) {
                $stockItem = StockItem::create([
                    'product_id'          => $validated['product_id'],
                    'quantity'            => $validated['quantity'],
                    'batch'               => $validated['batch'] ?? null,
                    'serial_number'       => $validated['serial_number'] ?? null,
                    'expiration_date'     => $validated['expiration_date'] ?? null,
                    'siscofis_entry_date' => $validated['siscofis_entry_date'] ?? null,
                    'location'            => $validated['location'] ?? null,
                    'subunit'             => $validated['subunit'] ?? null,
                    'status'              => 'available',
                    'notes'               => $validated['notes'] ?? null,
                ]);

                StockMovement::record(
                    stockItemId: $stockItem->id,
                    type: 'entry',
                    quantity: $validated['quantity'],
                    performedBy: Auth::id(),
                    notes: $validated['notes'] ?? 'Entrada de material no estoque',
                );
            });

            return redirect()
                ->route('stock.index')
                ->with('success', 'Entrada de material registrada com sucesso.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao registrar entrada. Tente novamente.');
        }
    }

    /**
     * Formulário de ajuste de estoque.
     */
    public function adjust(StockItem $stockItem)
    {
        $stockItem->load('product');

        return view('stock.adjust', compact('stockItem'));
    }

    /**
     * Processa ajuste de estoque.
     */
    public function storeAdjust(Request $request, StockItem $stockItem)
    {
        $validated = $request->validate([
            'new_quantity' => 'required|integer|min:0',
            'new_status'   => 'required|in:' . implode(',', array_keys(StockItem::STATUSES)),
            'reason'       => 'required|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($validated, $stockItem) {
                $oldQuantity = $stockItem->quantity;
                $diff        = $validated['new_quantity'] - $oldQuantity;

                $stockItem->update([
                    'quantity' => $validated['new_quantity'],
                    'status'   => $validated['new_status'],
                ]);

                StockMovement::record(
                    stockItemId: $stockItem->id,
                    type: 'adjustment',
                    quantity: $diff,
                    performedBy: Auth::id(),
                    notes: "Ajuste: {$validated['reason']} (de {$oldQuantity} para {$validated['new_quantity']})",
                );
            });

            return redirect()
                ->route('stock.show', $stockItem)
                ->with('success', 'Ajuste de estoque realizado com sucesso.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao realizar ajuste. Tente novamente.');
        }
    }

    /**
     * Listagem de movimentações do estoque.
     */
    public function movements(Request $request)
    {
        $query = StockMovement::with(['stockItem.product', 'performedBy']);

        // Filtro por tipo de movimentação
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        // Filtro por período
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->forDateRange($request->date_from, $request->date_to);
        }

        // Busca por produto
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('stockItem.product', function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%");
            });
        }

        $movements = $query->latest()->paginate(20)->appends($request->query());
        $types     = StockMovement::TYPES;

        return view('stock.movements', compact('movements', 'types'));
    }

    /**
     * Gera etiqueta com QR Code para um item de estoque.
     */
    public function label(StockItem $stockItem)
    {
        $stockItem->load('product');

        $url = route('stock.show', $stockItem);

        $qrCode = QrCode::format('svg')
            ->size(200)
            ->errorCorrection('M')
            ->generate($url);

        return view('stock.label', compact('stockItem', 'qrCode'));
    }

    /**
     * Gera etiquetas em lote (PDF) para múltiplos itens.
     */
    public function labelsBatch(Request $request)
    {
        $validated = $request->validate([
            'items'   => 'required|array|min:1',
            'items.*' => 'exists:stock_items,id',
        ]);

        $items   = StockItem::with('product')->whereIn('id', $validated['items'])->get();
        $qrCodes = [];

        foreach ($items as $item) {
            $url = route('stock.show', $item);
            $qrCodes[$item->id] = QrCode::format('svg')
                ->size(200)
                ->errorCorrection('M')
                ->generate($url);
        }

        $stockItem     = $items->first();
        $multipleItems = true;

        return view('stock.label', compact('items', 'qrCodes', 'stockItem', 'multipleItems'));
    }
}
