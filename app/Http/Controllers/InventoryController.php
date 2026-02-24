<?php

namespace App\Http\Controllers;

use App\Exports\MaterialSearchExport;
use App\Exports\MonthlyConsolidatedExport;
use App\Models\InventoryItem;
use App\Models\InventoryUpload;
use App\Models\Product;
use App\Models\StockItem;
use App\Services\InventoryPdfParser;
use App\Services\InventoryDurableSyncService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class InventoryController extends Controller
{
    /**
     * Listagem de inventários carregados.
     */
    public function index(Request $request)
    {
        $statuses = InventoryUpload::STATUSES;

        // Estatísticas globais
        $stats = [
            'total_uploads'   => InventoryUpload::count(),
            'completed'       => InventoryUpload::where('status', 'completed')->count(),
            'total_items'     => InventoryItem::count(),
            'total_value'     => InventoryUpload::where('status', 'completed')->sum('total_value'),
        ];

        // Se há filtro de mês, exibir uploads daquele mês
        if ($request->filled('month')) {
            $query = InventoryUpload::with('uploader')->recent();
            
            // Filtro por mês específico
            [$year, $month] = explode('-', $request->month);
            $query->whereYear('created_at', $year)
                  ->whereMonth('created_at', $month);

            // Filtros adicionais
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

            $uploads = $query->get();
            $selectedMonth = \Carbon\Carbon::create($year, $month)->locale('pt_BR')->isoFormat('MMMM [de] YYYY');

            return view('inventory.index', compact('uploads', 'statuses', 'stats', 'selectedMonth'));
        }

        // Sem filtro: exibir cards de meses disponíveis
        $months = InventoryUpload::selectRaw('EXTRACT(YEAR FROM created_at) as year, EXTRACT(MONTH FROM created_at) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->map(function ($item) {
                $yearMonth = sprintf('%d-%02d', $item->year, $item->month);
                return [
                    'year_month' => $yearMonth,
                    'label'      => \Carbon\Carbon::create($item->year, $item->month)->locale('pt_BR')->isoFormat('MMMM [de] YYYY'),
                    'count'      => $item->count,
                ];
            });

        return view('inventory.index', compact('months', 'statuses', 'stats'));
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
            'subunit'     => Auth::user()->subunit,
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

                // Salvar Material Permanente em inventory_items
                foreach ($result['permanent_items'] as $itemData) {
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

                // Salvar Material de Uso Duradouro em durable_goods_inventory
                foreach ($result['durable_goods'] as $itemData) {
                    \App\Models\DurableGoodsInventory::create([
                        'inventory_upload_id' => $upload->id,
                        'subunit'             => Auth::user()->subunit,
                        'material_name'       => $itemData['material_name'],
                        'ficha_number'        => $itemData['ficha_number'],
                        'material_code'       => $itemData['material_code'],
                        'accounting_account'  => $itemData['accounting_account'],
                        'quantity'            => $itemData['quantity'],
                        'unit_value'          => $itemData['unit_value'],
                        'total_value'         => $itemData['total_value'],
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

            // Sincronizar itens de uso duradouro com a tabela products
            $syncService = new InventoryDurableSyncService();
            $syncStats = $syncService->sync($upload->id);

            $successMessage = "Inventário processado com sucesso! {$upload->total_items} itens importados.";
            if ($syncStats['created'] > 0) {
                $successMessage .= " {$syncStats['created']} produtos duráveis criados com estoque.";
            }
            if ($syncStats['stock_gap_filled'] > 0) {
                $successMessage .= " {$syncStats['stock_gap_filled']} produto(s) com estoque faltando foram completados.";
            }
            if ($syncStats['already_exists'] > 0) {
                $successMessage .= " {$syncStats['already_exists']} já existiam no estoque.";
            }

            return redirect()->route('inventory.show', $upload)
                ->with('success', $successMessage);

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
     * Sync manual de duráveis: reconcilia produtos/estoque sem duplicar.
     */
    public function syncDurables(InventoryUpload $inventoryUpload)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Acesso restrito.');
        }

        $syncService = new InventoryDurableSyncService();
        $stats = $syncService->sync($inventoryUpload->id);

        $parts = [];
        if ($stats['created'] > 0)          $parts[] = "{$stats['created']} produto(s) novo(s) adicionados ao estoque";
        if ($stats['stock_gap_filled'] > 0) $parts[] = "{$stats['stock_gap_filled']} produto(s) sem estoque foram completados";
        if ($stats['qty_adjusted'] > 0)     $parts[] = "{$stats['qty_adjusted']} produto(s) com quantidade ajustada (+{$stats['qty_added']} unidades)";
        if ($stats['qty_surplus'] > 0)      $parts[] = "{$stats['qty_surplus']} produto(s) com estoque acima do inventário (não alterado)";
        if ($stats['in_sync'] > 0)          $parts[] = "{$stats['in_sync']} produto(s) já em sincronia";
        if ($stats['skipped_zero'] > 0)     $parts[] = "{$stats['skipped_zero']} ignorados (qtd zero)";
        if ($stats['errors'] > 0)           $parts[] = "{$stats['errors']} erro(s)";

        $hasChanges = $stats['created'] > 0 || $stats['stock_gap_filled'] > 0 || $stats['qty_adjusted'] > 0;
        $msg = 'Sync Duráveis concluído. ' . ($parts ? implode(', ', $parts) . '.' : 'Nenhuma alteração necessária.');

        if ($stats['errors'] > 0 && !$hasChanges) {
            $key = 'error';
        } elseif ($hasChanges) {
            $key = 'success';
        } elseif ($stats['qty_surplus'] > 0) {
            $key = 'warning';  // estoque acima do inventário — requer atenção
        } else {
            $key = 'info';  // tudo já estava em sincronia
        }

        return redirect()->route('inventory.show', $inventoryUpload)->with($key, $msg);
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
            // Deletar itens antigos de ambas as tabelas
            $inventory->items()->delete();
            $inventory->durableGoods()->delete();

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

                // Salvar Material Permanente em inventory_items
                foreach ($result['permanent_items'] as $itemData) {
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

                // Salvar Material de Uso Duradouro em durable_goods_inventory
                foreach ($result['durable_goods'] as $itemData) {
                    \App\Models\DurableGoodsInventory::create([
                        'inventory_upload_id' => $inventory->id,
                        'subunit'             => $inventory->uploader->subunit ?? Auth::user()->subunit,
                        'material_name'       => $itemData['material_name'],
                        'ficha_number'        => $itemData['ficha_number'],
                        'material_code'       => $itemData['material_code'],
                        'accounting_account'  => $itemData['accounting_account'],
                        'quantity'            => $itemData['quantity'],
                        'unit_value'          => $itemData['unit_value'],
                        'total_value'         => $itemData['total_value'],
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

            // Sincronizar itens de uso duradouro com a tabela products
            $syncService = new InventoryDurableSyncService();
            $syncStats = $syncService->sync($inventory->id);

            $successMessage = "Inventário reprocessado! {$inventory->total_items} itens.";
            if ($syncStats['created'] > 0) {
                $successMessage .= " {$syncStats['created']} produtos duráveis adicionados ao estoque.";
            }
            if ($syncStats['stock_gap_filled'] > 0) {
                $successMessage .= " {$syncStats['stock_gap_filled']} produto(s) com estoque faltando foram completados.";
            }
            if ($syncStats['qty_adjusted'] > 0) {
                $successMessage .= " {$syncStats['qty_adjusted']} produto(s) tiveram quantidade ajustada (+{$syncStats['qty_added']} unidades).";
            }
            if ($syncStats['in_sync'] > 0 && $syncStats['created'] === 0 && $syncStats['qty_adjusted'] === 0) {
                $successMessage .= " {$syncStats['in_sync']} produto(s) duráveis já estavam em sincronia.";
            }

            return redirect()->route('inventory.show', $inventory)
                ->with('success', $successMessage);

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

    /**
     * Busca de materiais entre todos os inventários.
     */
    public function searchMaterials(Request $request)
    {
        $query = InventoryItem::with(['upload']);

        // Excluir itens ocultos nesta sessão
        $hiddenItems = session('hidden_inventory_items', []);
        if (!empty($hiddenItems)) {
            $query->whereNotIn('id', $hiddenItems);
        }

        // Filtro de busca inteligente
        if ($request->filled('search')) {
            $search = trim($request->search);
            
            // Dividir busca em palavras individuais
            $words = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);
            
            $query->where(function ($q) use ($search, $words) {
                // Busca exata (maior prioridade)
                $q->whereRaw('unaccent(LOWER(material_name)) LIKE unaccent(LOWER(?))', ["%{$search}%"])
                  ->orWhereRaw('unaccent(LOWER(material_code)) LIKE unaccent(LOWER(?))', ["%{$search}%"])
                  ->orWhereRaw('unaccent(LOWER(ficha_number)) LIKE unaccent(LOWER(?))', ["%{$search}%"]);
                
                // Busca por palavras individuais (múltiplas palavras)
                if (count($words) > 1) {
                    $q->orWhere(function ($subQ) use ($words) {
                        foreach ($words as $word) {
                            if (strlen($word) >= 3) { // só palavras com 3+ caracteres
                                $subQ->whereRaw('unaccent(LOWER(material_name)) LIKE unaccent(LOWER(?))', ["%{$word}%"]);
                            }
                        }
                    });
                }
                
                // Busca por similaridade (typos, aproximações) - threshold mais baixo
                $q->orWhereRaw('similarity(unaccent(LOWER(material_name)), unaccent(LOWER(?))) > 0.15', [$search]);
            });
            
            // Ordenar por relevância
            $query->selectRaw('inventory_items.*, similarity(unaccent(LOWER(material_name)), unaccent(LOWER(?))) as relevance_score', [$search])
                  ->orderByDesc('relevance_score');
        }

        // Filtro por tipo de material
        if ($request->filled('material_type')) {
            $query->where('material_type', $request->material_type);
        }

        // Filtro por dependência
        if ($request->filled('dependency')) {
            $query->whereHas('upload', function ($q) use ($request) {
                $q->where('dependency', 'ilike', "%{$request->dependency}%");
            });
        }

        // Filtro por unidade
        if ($request->filled('unit')) {
            $query->whereHas('upload', function ($q) use ($request) {
                $q->where('unit', 'ilike', "%{$request->unit}%");
            });
        }

        // Ordenação (só aplicar se não houver busca, pois busca já ordena por relevância)
        if (!$request->filled('search')) {
            $orderBy = $request->get('order_by', 'material_name');
            $orderDir = $request->get('order_dir', 'asc');
            
            if ($orderBy === 'total_value') {
                $query->orderBy('total_value', $orderDir);
            } elseif ($orderBy === 'quantity') {
                $query->orderBy('quantity', $orderDir);
            } else {
                $query->orderBy('material_name', $orderDir);
            }
        } else {
            // Ordenação secundária após relevância
            $orderBy = $request->get('order_by');
            $orderDir = $request->get('order_dir', 'desc');
            
            if ($orderBy === 'total_value') {
                $query->orderBy('total_value', $orderDir);
            } elseif ($orderBy === 'quantity') {
                $query->orderBy('quantity', $orderDir);
            }
        }

        $items = $query->paginate(50)->appends($request->query());

        // Tipos de materiais disponíveis
        $materialTypes = InventoryItem::select('material_type')
            ->distinct()
            ->whereNotNull('material_type')
            ->pluck('material_type')
            ->sort()
            ->values();

        // Dependências disponíveis
        $dependencies = InventoryUpload::select('dependency')
            ->distinct()
            ->whereNotNull('dependency')
            ->pluck('dependency')
            ->sort()
            ->values();

        // Estatísticas
        $stats = [
            'total_items'    => InventoryItem::count(),
            'total_value'    => InventoryItem::sum('total_value'),
            'total_quantity' => InventoryItem::sum('quantity'),
            'unique_materials' => InventoryItem::distinct('material_name')->count('material_name'),
        ];

        return view('inventory.materials', compact('items', 'materialTypes', 'dependencies', 'stats'));
    }

    /**
     * Página de relatórios.
     */
    public function reportsIndex()
    {
        // Uploads disponíveis para relatórios
        $uploads = InventoryUpload::where('status', 'completed')
            ->orderByDesc('created_at')
            ->get();

        // Tipos de materiais
        $materialTypes = InventoryItem::select('material_type')
            ->distinct()
            ->whereNotNull('material_type')
            ->pluck('material_type')
            ->sort()
            ->values();

        // Dependências
        $dependencies = InventoryUpload::select('dependency')
            ->distinct()
            ->whereNotNull('dependency')
            ->pluck('dependency')
            ->sort()
            ->values();

        return view('inventory.reports', compact('uploads', 'materialTypes', 'dependencies'));
    }

    /**
     * Gerar relatório baseado nos filtros.
     */
    public function generateReport(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:general,by_material,by_upload,by_dependency,monthly_consolidated',
            'format'      => 'required|in:pdf,excel',
            'month_year'  => 'nullable|date_format:Y-m',
        ]);

        // Tratamento especial para relatório consolidado mensal
        if ($request->report_type === 'monthly_consolidated') {
            return $this->generateMonthlyConsolidatedReport($request);
        }

        $query = InventoryItem::with(['upload']);

        // Aplicar filtros
        if ($request->filled('upload_id')) {
            $query->where('inventory_upload_id', $request->upload_id);
        }

        if ($request->filled('material_type')) {
            $query->where('material_type', $request->material_type);
        }

        if ($request->filled('dependency')) {
            $query->whereHas('upload', function ($q) use ($request) {
                $q->where('dependency', 'ilike', "%{$request->dependency}%");
            });
        }

        // Busca ampla: campo 1 OU campo 2 OU campo 3
        // Cada campo busca em nome, código ou ficha
        if ($request->filled('search_1') || $request->filled('search_2') || $request->filled('search_3')) {
            $query->where(function ($q) use ($request) {
                // Campo 1
                if ($request->filled('search_1')) {
                    $search1 = $request->search_1;
                    $q->orWhere(function ($subQ) use ($search1) {
                        $subQ->where('material_name', 'ilike', "%{$search1}%")
                             ->orWhere('material_code', 'ilike', "%{$search1}%")
                             ->orWhere('ficha_number', 'ilike', "%{$search1}%");
                    });
                }

                // Campo 2
                if ($request->filled('search_2')) {
                    $search2 = $request->search_2;
                    $q->orWhere(function ($subQ) use ($search2) {
                        $subQ->where('material_name', 'ilike', "%{$search2}%")
                             ->orWhere('material_code', 'ilike', "%{$search2}%")
                             ->orWhere('ficha_number', 'ilike', "%{$search2}%");
                    });
                }

                // Campo 3
                if ($request->filled('search_3')) {
                    $search3 = $request->search_3;
                    $q->orWhere(function ($subQ) use ($search3) {
                        $subQ->where('material_name', 'ilike', "%{$search3}%")
                             ->orWhere('material_code', 'ilike', "%{$search3}%")
                             ->orWhere('ficha_number', 'ilike', "%{$search3}%");
                    });
                }
            });
        }

        $items = $query->orderBy('material_name')->get();

        if ($items->isEmpty()) {
            return back()->with('error', 'Nenhum item encontrado com os filtros aplicados.');
        }

        // Estatísticas
        $stats = [
            'total_items'    => $items->count(),
            'total_quantity' => $items->sum('quantity'),
            'total_value'    => $items->sum('total_value'),
        ];

        $reportTitle = match($request->report_type) {
            'general'       => 'Relatório Geral de Inventário',
            'by_material'   => 'Relatório por Tipo de Material',
            'by_upload'     => 'Relatório por Upload',
            'by_dependency' => 'Relatório por Dependência',
        };

        // Gerar PDF
        if ($request->format === 'pdf') {
            $pdf = \PDF::loadView('inventory.reports.pdf', compact('items', 'stats', 'reportTitle'));
            return $pdf->setPaper('a4', 'landscape')->stream('relatorio_inventario_' . date('Ymd_His') . '.pdf');
        }

        // Gerar Excel
        if ($request->format === 'excel') {
            return \Excel::download(
                new \App\Exports\InventoryReportExport($items, $stats, $reportTitle),
                'relatorio_inventario_' . date('Ymd_His') . '.xlsx'
            );
        }
    }

    /**
     * Gerar relatório consolidado mensal (todas as dependências de um mês).
     */
    protected function generateMonthlyConsolidatedReport(Request $request)
    {
        $monthYear = $request->month_year ?? now()->format('Y-m');
        
        // Parsear mês e ano
        [$year, $month] = explode('-', $monthYear);
        
        // Buscar todos os uploads do mês
        $uploads = InventoryUpload::where('status', 'completed')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->with('items')
            ->orderBy('dependency')
            ->get();

        if ($uploads->isEmpty()) {
            return back()->with('error', 'Nenhum inventário encontrado para ' . $monthYear);
        }

        // Coletar todos os itens
        $allItems = $uploads->flatMap->items;

        // Agrupar por dependência
        $itemsByDependency = $uploads->mapWithKeys(function ($upload) {
            return [
                $upload->dependency => [
                    'upload' => $upload,
                    'items' => $upload->items,
                    'stats' => [
                        'total_items' => $upload->items->count(),
                        'total_quantity' => $upload->items->sum('quantity'),
                        'total_value' => $upload->items->sum('total_value'),
                    ]
                ]
            ];
        });

        // Estatísticas globais
        $globalStats = [
            'total_dependencies' => $uploads->unique('dependency')->count(),
            'total_uploads' => $uploads->count(),
            'total_items' => $allItems->count(),
            'total_quantity' => $allItems->sum('quantity'),
            'total_value' => $allItems->sum('total_value'),
        ];

        $monthName = \Carbon\Carbon::createFromFormat('Y-m', $monthYear)->locale('pt_BR')->isoFormat('MMMM/YYYY');
        $reportTitle = 'Consolidado Mensal — ' . ucfirst($monthName);

        // Gerar PDF
        if ($request->format === 'pdf') {
            $pdf = \PDF::loadView('inventory.reports.monthly-consolidated-pdf', compact(
                'itemsByDependency',
                'globalStats',
                'reportTitle',
                'monthYear',
                'monthName'
            ));
            
            return $pdf->setPaper('a4', 'landscape')
                ->stream('consolidado_' . $monthYear . '_' . date('Ymd_His') . '.pdf');
        }

        // Gerar Excel
        if ($request->format === 'excel') {
            return \Excel::download(
                new \App\Exports\MonthlyConsolidatedExport($itemsByDependency, $globalStats, $reportTitle, $monthName),
                'consolidado_' . $monthYear . '_' . date('Ymd_His') . '.xlsx'
            );
        }
    }

    /**
     * Formulário para editar localização (dependência/unidade) do upload.
     */
    public function editLocation(InventoryUpload $inventoryUpload)
    {
        $inventory = $inventoryUpload;
        return view('inventory.edit-location', compact('inventory'));
    }

    /**
     * Atualizar localização do upload.
     */
    public function updateLocation(Request $request, InventoryUpload $inventoryUpload)
    {
        $request->validate([
            'dependency' => 'nullable|string|max:255',
            'unit'       => 'nullable|string|max:255',
            'unit_code'  => 'nullable|string|max:50',
        ]);

        $inventoryUpload->update([
            'dependency' => $request->dependency,
            'unit'       => $request->unit,
            'unit_code'  => $request->unit_code,
        ]);

        return redirect()->route('inventory.show', $inventoryUpload)
            ->with('success', 'Localização atualizada com sucesso!');
    }

    /**
     * Exportar resultados da busca de materiais.
     */
    public function exportMaterials(Request $request)
    {
        $request->validate([
            'format' => 'required|in:pdf,excel',
            'search' => 'nullable|string',
            'type' => 'nullable|string',
        ]);

        // Replicar a mesma query do searchMaterials
        $query = InventoryItem::with(['upload']);

        // Excluir itens ocultos nesta sessão
        $hiddenItems = session('hidden_inventory_items', []);
        if (!empty($hiddenItems)) {
            $query->whereNotIn('id', $hiddenItems);
        }

        // Filtro de busca
        if ($request->filled('search')) {
            $search = trim($request->search);
            $words = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);
            
            $query->where(function ($q) use ($search, $words) {
                $q->whereRaw('unaccent(LOWER(material_name)) LIKE unaccent(LOWER(?))', ["%{$search}%"])
                  ->orWhereRaw('unaccent(LOWER(material_code)) LIKE unaccent(LOWER(?))', ["%{$search}%"])
                  ->orWhereRaw('unaccent(LOWER(ficha_number)) LIKE unaccent(LOWER(?))', ["%{$search}%"]);
                
                if (count($words) > 1) {
                    $q->orWhere(function ($subQ) use ($words) {
                        foreach ($words as $word) {
                            if (strlen($word) >= 3) {
                                $subQ->whereRaw('unaccent(LOWER(material_name)) LIKE unaccent(LOWER(?))', ["%{$word}%"]);
                            }
                        }
                    });
                }
                
                $q->orWhereRaw('similarity(unaccent(LOWER(material_name)), unaccent(LOWER(?))) > 0.15', [$search]);
            });
        }

        // Filtro por tipo
        if ($request->filled('type')) {
            $query->where('material_type', $request->type);
        }

        // Buscar todos os itens (sem paginação)
        $items = $query->orderBy('material_name')->get();

        // Estatísticas
        $stats = [
            'total_items' => $items->count(),
            'unique_materials' => $items->unique('material_name')->count(),
            'total_quantity' => $items->sum('quantity'),
            'total_value' => $items->sum('total_value'),
        ];

        $searchTerm = $request->search ?? '';

        // Exportar
        if ($request->format === 'excel') {
            $filename = 'busca_materiais_' . date('Y-m-d_His') . '.xlsx';
            return Excel::download(new MaterialSearchExport($items, $stats, $searchTerm), $filename);
        }

        // PDF
        $pdf = Pdf::loadView('inventory.materials-pdf', [
            'items' => $items,
            'stats' => $stats,
            'searchTerm' => $searchTerm,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ]);

        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('busca_materiais_' . date('Y-m-d_His') . '.pdf');
    }

    /**
     * Ocultar item temporariamente da busca (sessão).
     */
    public function hideItem(Request $request)
    {
        $itemId = $request->input('item_id');
        
        // Recuperar lista de itens ocultos da sessão
        $hiddenItems = session('hidden_inventory_items', []);
        
        // Adicionar ID à lista se ainda não estiver
        if (!in_array($itemId, $hiddenItems)) {
            $hiddenItems[] = $itemId;
            session(['hidden_inventory_items' => $hiddenItems]);
        }

        return redirect()->back()
            ->with('success', 'Item removido da visualização atual. Para ver novamente, clique em "Mostrar Todos".');
    }

    /**
     * Limpar lista de itens ocultos (mostrar todos novamente).
     */
    public function resetHidden()
    {
        session()->forget('hidden_inventory_items');

        return redirect()->back()
            ->with('success', 'Todos os itens ocultos foram restaurados na visualização!');
    }
}
