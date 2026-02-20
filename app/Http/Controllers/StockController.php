<?php

namespace App\Http\Controllers;

use App\Models\DurableGoodsInventory;
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
                    performedBy: Auth::user()->id,
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
                    performedBy: Auth::user()->id,
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
     * Atualiza dados de um item de estoque específico.
     */
    public function updateItem(Request $request, StockItem $stockItem)
    {
        $validated = $request->validate([
            'batch'              => 'nullable|string|max:100',
            'serial_number'      => 'nullable|string|max:100|unique:stock_items,serial_number,' . $stockItem->id,
            'siscofis_entry_date' => 'nullable|date',
            'location'           => 'nullable|string|max:255',
            'notes'              => 'nullable|string|max:1000',
        ]);

        try {
            $stockItem->update($validated);

            // Recalcular expiration_date se necessário (via model event)
            if (isset($validated['siscofis_entry_date'])) {
                $stockItem->save(); // Trigger events
            }

            return redirect()
                ->back()
                ->with('success', 'Item de estoque atualizado com sucesso!');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar item: ' . $e->getMessage());
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

    /**
     * Listagem de produtos de uso duradouro (is_durable = true).
     */
    public function durables(Request $request)
    {
        // Produtos duráveis
        $durableProducts = Product::where('is_durable', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        // Itens de estoque por produto durável com agregações
        $durableItems = collect();

        foreach ($durableProducts as $product) {
            $stockItems = StockItem::where('product_id', $product->id)
                ->with('product.category')
                ->get();

            // Buscar dados do último inventário SISCOFIS para este produto (somar todos os lotes)
            $inventoryQty = 0;
            $inventoryValue = 0;
            $inventoryDate = null;
            
            if ($product->siscofis_code) {
                $inventoryRecords = DurableGoodsInventory::where('material_code', $product->siscofis_code)
                    ->get();
                
                $inventoryQty = $inventoryRecords->sum('quantity') ?? 0;
                $inventoryValue = $inventoryRecords->sum('total_value') ?? 0;
                $inventoryDate = $inventoryRecords->max('created_at');
            }

            // Quantidades em stock_items (controle individual)
            $totalQuantity = $stockItems->sum('quantity');
            $availableQty  = $stockItems->where('status', 'available')->sum('quantity');
            $loanedQty     = $stockItems->where('status', 'loaned')->sum('quantity');
            $damagedQty    = $stockItems->where('status', 'damaged')->sum('quantity');

            // Itens próximos do vencimento (30 dias)
            $expiringSoon = $stockItems->filter(function ($item) {
                return $item->expiration_date && $item->expiration_date->lte(now()->addDays(30)) && $item->expiration_date->gt(now()) && $item->status === 'available';
            })->count();

            // Itens vencidos
            $expired = $stockItems->filter(function ($item) {
                return $item->expiration_date && $item->expiration_date->lt(now()) && $item->status === 'available';
            })->count();

            $durableItems->push((object) [
                'product'          => $product,
                'inventory_qty'    => $inventoryQty,
                'inventory_value'  => $inventoryValue,
                'inventory_date'   => $inventoryDate,
                'total'            => $totalQuantity,
                'available'        => $availableQty,
                'loaned'           => $loanedQty,
                'damaged'          => $damagedQty,
                'expiring_soon'    => $expiringSoon,
                'expired'          => $expired,
                'items'            => $stockItems,
            ]);
        }

        // Ordenação
        $sortBy = $request->get('sort', 'name');
        $sortDir = $request->get('dir', 'asc');

        $durableItems = $durableItems->sortBy(function ($item) use ($sortBy) {
            switch ($sortBy) {
                case 'total': return $item->total;
                case 'available': return $item->available;
                case 'loaned': return $item->loaned;
                case 'expiring': return $item->expiring_soon + $item->expired;
                default: return $item->product->name;
            }
        }, SORT_REGULAR, $sortDir === 'desc');

        return view('stock.durables', compact('durableItems', 'durableProducts'));
    }
}
