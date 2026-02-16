<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryUpload;
use App\Models\Product;
use App\Models\StockItem;
use App\Services\InventoryPdfParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    /**
     * Listagem de inventários carregados.
     */
    public function index(Request $request)
    {
        $query = InventoryUpload::with('uploader')->recent();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('filename', 'ilike', "%{$search}%")
                  ->orWhere('dependency', 'ilike', "%{$search}%")
                  ->orWhere('unit', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $uploads  = $query->paginate(20)->appends($request->query());
        $statuses = InventoryUpload::STATUSES;

        // Estatísticas globais
        $stats = [
            'total_uploads'   => InventoryUpload::count(),
            'completed'       => InventoryUpload::where('status', 'completed')->count(),
            'total_items'     => InventoryItem::count(),
            'total_value'     => InventoryUpload::where('status', 'completed')->sum('total_value'),
        ];

        return view('inventory.index', compact('uploads', 'statuses', 'stats'));
    }

    /**
     * Formulário de upload de PDF.
     */
    public function create()
    {
        return view('inventory.upload');
    }

    /**
     * Processa upload do PDF, parseia e salva no banco.
     */
    public function store(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:20480', // max 20MB
        ], [
            'pdf_file.required' => 'Selecione um arquivo PDF.',
            'pdf_file.mimes'    => 'O arquivo deve ser um PDF.',
            'pdf_file.max'      => 'O arquivo não pode exceder 20MB.',
        ]);

        $file         = $request->file('pdf_file');
        $originalName = $file->getClientOriginalName();

        // Salvar PDF em storage/app/inventario
        $filename = date('Ymd_His') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $storedPath = $file->storeAs('', $filename, 'inventario');

        // Criar registro do upload
        $upload = InventoryUpload::create([
            'filename'    => $originalName,
            'stored_path' => $storedPath,
            'uploaded_by' => Auth::user()->id,
            'status'      => 'processing',
        ]);

        try {
            // Parsear o PDF
            $parser = new InventoryPdfParser();
            $fullPath = storage_path('app/inventario/' . $storedPath);
            $result = $parser->parse($fullPath);

            // Atualizar cabeçalho
            $upload->update([
                'dependency' => $result['header']['dependency'],
                'unit'       => $result['header']['unit'],
                'unit_code'  => $result['header']['unit_code'],
            ]);

            // Salvar itens no banco dentro de uma transação
            DB::transaction(function () use ($upload, $result) {
                $totalValue = 0;
                $totalItems = 0;

                foreach ($result['items'] as $itemData) {
                    InventoryItem::create([
                        'inventory_upload_id' => $upload->id,
                        'material_type'       => $itemData['material_type'],
                        'material_name'       => $itemData['material_name'],
                        'ficha_number'        => $itemData['ficha_number'],
                        'material_code'       => $itemData['material_code'],
                        'accounting_account'  => $itemData['accounting_account'],
                        'acervo'              => $itemData['acervo'],
                        'quantity'            => $itemData['quantity'],
                        'unit_value'          => $itemData['unit_value'],
                        'total_value'         => $itemData['total_value'],
                        'patrimony_numbers'   => $itemData['patrimony_numbers'],
                        'raw_text'            => $itemData['raw_text'],
                    ]);

                    $totalValue += $itemData['total_value'];
                    $totalItems++;
                }

                $upload->update([
                    'status'       => 'completed',
                    'total_items'  => $totalItems,
                    'total_value'  => $totalValue,
                    'processed_at' => now(),
                ]);
            });

            return redirect()->route('inventory.show', $upload)
                ->with('success', "Inventário processado com sucesso! {$upload->total_items} itens importados.");

        } catch (\Exception $e) {
            $upload->update([
                'status'        => 'error',
                'error_message' => $e->getMessage(),
                'processed_at'  => now(),
            ]);

            return redirect()->route('inventory.index')
                ->with('error', "Erro ao processar PDF: {$e->getMessage()}");
        }
    }

    /**
     * Visualizar detalhes de um upload e seus itens.
     */
    public function show(InventoryUpload $inventoryUpload)
    {
        $inventoryUpload->load(['items', 'uploader']);

        // Agrupar itens por tipo de material
        $groupedItems = $inventoryUpload->items->groupBy('material_type');

        // Estatísticas do upload
        $stats = [
            'total_items'    => $inventoryUpload->items->count(),
            'total_quantity' => $inventoryUpload->items->sum('quantity'),
            'total_value'    => $inventoryUpload->items->sum('total_value'),
            'types'          => $groupedItems->keys()->toArray(),
            'patrimony_count' => $inventoryUpload->items->sum(fn ($item) => count($item->patrimony_numbers ?? [])),
        ];

        $inventory = $inventoryUpload;
        return view('inventory.show', compact('inventory', 'groupedItems', 'stats'));
    }

    /**
     * Reprocessar um PDF (deletar itens e re-parsear).
     */
    public function reprocess(InventoryUpload $inventoryUpload)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Apenas administradores podem reprocessar inventários.');
        }

        $inventory = $inventoryUpload;

        try {
            // Deletar itens antigos
            $inventory->items()->delete();

            $parser = new InventoryPdfParser();
            $fullPath = storage_path('app/inventario/' . $inventory->stored_path);
            $result = $parser->parse($fullPath);

            $inventory->update([
                'dependency' => $result['header']['dependency'],
                'unit'       => $result['header']['unit'],
                'unit_code'  => $result['header']['unit_code'],
                'status'     => 'processing',
            ]);

            DB::transaction(function () use ($inventory, $result) {
                $totalValue = 0;
                $totalItems = 0;

                foreach ($result['items'] as $itemData) {
                    InventoryItem::create([
                        'inventory_upload_id' => $inventory->id,
                        'material_type'       => $itemData['material_type'],
                        'material_name'       => $itemData['material_name'],
                        'ficha_number'        => $itemData['ficha_number'],
                        'material_code'       => $itemData['material_code'],
                        'accounting_account'  => $itemData['accounting_account'],
                        'acervo'              => $itemData['acervo'],
                        'quantity'            => $itemData['quantity'],
                        'unit_value'          => $itemData['unit_value'],
                        'total_value'         => $itemData['total_value'],
                        'patrimony_numbers'   => $itemData['patrimony_numbers'],
                        'raw_text'            => $itemData['raw_text'],
                    ]);

                    $totalValue += $itemData['total_value'];
                    $totalItems++;
                }

                $inventory->update([
                    'status'       => 'completed',
                    'total_items'  => $totalItems,
                    'total_value'  => $totalValue,
                    'processed_at' => now(),
                    'error_message' => null,
                ]);
            });

            return redirect()->route('inventory.show', $inventory)
                ->with('success', "Inventário reprocessado! {$inventory->total_items} itens.");

        } catch (\Exception $e) {
            $inventory->update([
                'status'        => 'error',
                'error_message' => $e->getMessage(),
                'processed_at'  => now(),
            ]);

            return redirect()->route('inventory.show', $inventory)
                ->with('error', "Erro ao reprocessar: {$e->getMessage()}");
        }
    }

    /**
     * Excluir um upload e seus itens.
     */
    public function destroy(InventoryUpload $inventoryUpload)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Apenas administradores podem excluir inventários.');
        }

        $inventory = $inventoryUpload;
        $filename = $inventory->filename;

        // Deletar arquivo do storage
        if (Storage::disk('inventario')->exists($inventory->stored_path)) {
            Storage::disk('inventario')->delete($inventory->stored_path);
        }

        $inventory->delete(); // cascade deleta os items

        return redirect()->route('inventory.index')
            ->with('success', "Inventário \"{$filename}\" excluído.");
    }

    /**
     * Download do PDF original.
     */
    public function download(InventoryUpload $inventoryUpload)
    {
        $inventory = $inventoryUpload;
        $path = storage_path('app/inventario/' . $inventory->stored_path);

        if (!file_exists($path)) {
            return redirect()->route('inventory.show', $inventory)
                ->with('error', 'Arquivo PDF não encontrado no servidor.');
        }

        return response()->download($path, $inventory->filename);
    }

    // ══════════════════════════════════════════════════════════
    //  Comparação de Inventários
    // ══════════════════════════════════════════════════════════

    /**
     * Formulário para selecionar dois inventários para comparação.
     */
    public function compareForm(Request $request)
    {
        $uploads = InventoryUpload::completed()
            ->recent()
            ->get(['id', 'filename', 'dependency', 'unit', 'unit_code', 'total_items', 'total_value', 'created_at']);

        // Pré-selecionar se veio da query string
        $selectedOld = $request->get('old');
        $selectedNew = $request->get('new');

        return view('inventory.compare', compact('uploads', 'selectedOld', 'selectedNew'));
    }

    /**
     * Comparar dois inventários: exibir entradas, saídas e alterações.
     */
    public function compareResults(Request $request)
    {
        $request->validate([
            'old_upload_id' => 'required|exists:inventory_uploads,id',
            'new_upload_id' => 'required|exists:inventory_uploads,id|different:old_upload_id',
        ], [
            'old_upload_id.required' => 'Selecione o inventário anterior.',
            'new_upload_id.required' => 'Selecione o inventário mais recente.',
            'new_upload_id.different' => 'Os dois inventários devem ser diferentes.',
        ]);

        $oldUpload = InventoryUpload::with('items')->findOrFail($request->old_upload_id);
        $newUpload = InventoryUpload::with('items')->findOrFail($request->new_upload_id);

        // Indexar itens por material_code para comparação
        $oldItems = $oldUpload->items->keyBy('material_code');
        $newItems = $newUpload->items->keyBy('material_code');

        $allCodes = $oldItems->keys()->merge($newItems->keys())->unique();

        $added    = collect(); // Só no novo
        $removed  = collect(); // Só no antigo
        $changed  = collect(); // Em ambos, com diferenças
        $unchanged = collect(); // Em ambos, sem diferenças

        foreach ($allCodes as $code) {
            $oldItem = $oldItems->get($code);
            $newItem = $newItems->get($code);

            if (!$oldItem && $newItem) {
                $added->push([
                    'item' => $newItem,
                    'type' => 'added',
                ]);
            } elseif ($oldItem && !$newItem) {
                $removed->push([
                    'item' => $oldItem,
                    'type' => 'removed',
                ]);
            } else {
                // Ambos existem — verificar diferenças
                $qtyDiff   = $newItem->quantity - $oldItem->quantity;
                $valueDiff = $newItem->total_value - $oldItem->total_value;

                $oldPatrimonies = collect($oldItem->patrimony_numbers ?? []);
                $newPatrimonies = collect($newItem->patrimony_numbers ?? []);
                $addedPatrimonies   = $newPatrimonies->diff($oldPatrimonies)->values();
                $removedPatrimonies = $oldPatrimonies->diff($newPatrimonies)->values();

                $hasChanges = $qtyDiff !== 0
                    || round($valueDiff, 2) != 0
                    || $addedPatrimonies->isNotEmpty()
                    || $removedPatrimonies->isNotEmpty();

                $entry = [
                    'old'  => $oldItem,
                    'new'  => $newItem,
                    'qty_diff'             => $qtyDiff,
                    'value_diff'           => $valueDiff,
                    'added_patrimonies'    => $addedPatrimonies,
                    'removed_patrimonies'  => $removedPatrimonies,
                ];

                if ($hasChanges) {
                    $changed->push($entry);
                } else {
                    $unchanged->push($entry);
                }
            }
        }

        // Resumo por tipo de material
        $summary = [
            'added_count'   => $added->count(),
            'removed_count' => $removed->count(),
            'changed_count' => $changed->count(),
            'unchanged_count' => $unchanged->count(),
            'added_value'   => $added->sum(fn ($a) => $a['item']->total_value),
            'removed_value' => $removed->sum(fn ($r) => $r['item']->total_value),
            'value_diff'    => $newUpload->total_value - $oldUpload->total_value,
            'qty_diff'      => $newUpload->items->sum('quantity') - $oldUpload->items->sum('quantity'),
        ];

        return view('inventory.compare-results', compact(
            'oldUpload', 'newUpload',
            'added', 'removed', 'changed', 'unchanged',
            'summary'
        ));
    }

    /**
     * Comparar itens de Uso Duradouro de um inventário com o estoque atual.
     */
    public function compareDurables(InventoryUpload $inventoryUpload)
    {
        $inventoryUpload->load('items');

        // Filtrar itens de uso duradouro do inventário
        $durableItems = $inventoryUpload->items
            ->filter(fn ($item) => str_contains(strtoupper($item->material_type), 'DURADOURO'))
            ->values();

        // Buscar produtos duráveis do sistema com estoque
        $durableProducts = Product::where('is_durable', true)
            ->with(['category', 'stockItems'])
            ->orderBy('name')
            ->get();

        // Tentar cruzar por siscofis_code ↔ material_code
        $productsByCode = $durableProducts->keyBy('siscofis_code');

        $matched   = collect(); // Encontrado em ambos
        $onlyInventory = collect(); // Apenas no inventário PDF
        $onlySystem    = collect(); // Apenas no sistema

        $matchedCodes = collect();

        foreach ($durableItems as $invItem) {
            $product = $productsByCode->get($invItem->material_code);

            if ($product) {
                $stockQty   = $product->stockItems->sum('quantity');
                $availQty   = $product->stockItems->where('status', 'available')->sum('quantity');
                $loanedQty  = $product->stockItems->where('status', 'loaned')->sum('quantity');
                $damagedQty = $product->stockItems->where('status', 'damaged')->sum('quantity');

                $matched->push([
                    'inventory_item' => $invItem,
                    'product'        => $product,
                    'system_total'   => $stockQty,
                    'system_available' => $availQty,
                    'system_loaned'  => $loanedQty,
                    'system_damaged' => $damagedQty,
                    'qty_diff'       => $invItem->quantity - $stockQty,
                    'value_system'   => $product->stockItems->sum(fn ($si) => $si->quantity * ($si->unit_price ?? 0)),
                ]);

                $matchedCodes->push($product->siscofis_code);
            } else {
                $onlyInventory->push($invItem);
            }
        }

        // Produtos duráveis no sistema que não foram encontrados no inventário
        foreach ($durableProducts as $product) {
            if (!$matchedCodes->contains($product->siscofis_code)) {
                $stockQty = $product->stockItems->sum('quantity');
                if ($stockQty > 0) {
                    $onlySystem->push([
                        'product'        => $product,
                        'system_total'   => $stockQty,
                        'system_available' => $product->stockItems->where('status', 'available')->sum('quantity'),
                        'system_loaned'  => $product->stockItems->where('status', 'loaned')->sum('quantity'),
                        'system_damaged' => $product->stockItems->where('status', 'damaged')->sum('quantity'),
                    ]);
                }
            }
        }

        $summary = [
            'matched_count'   => $matched->count(),
            'only_inv_count'  => $onlyInventory->count(),
            'only_sys_count'  => $onlySystem->count(),
            'inv_total_qty'   => $durableItems->sum('quantity'),
            'inv_total_value' => $durableItems->sum('total_value'),
            'sys_total_qty'   => $durableProducts->sum(fn ($p) => $p->stockItems->sum('quantity')),
        ];

        $inventory = $inventoryUpload;

        return view('inventory.compare-durables', compact(
            'inventory', 'durableItems',
            'matched', 'onlyInventory', 'onlySystem',
            'summary'
        ));
    }
}
