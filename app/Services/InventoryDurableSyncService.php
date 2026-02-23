<?php

namespace App\Services;

use App\Models\InventoryUpload;
use App\Models\DurableGoodsInventory;
use App\Models\Product;
use App\Models\Category;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Sincroniza itens de "Material de Uso Duráduro" do inventário
 * com a tabela de produtos (products) e estoque (stock_items).
 *
 * Lógica de reconciliação (por item):
 *   - Produto não existe  → cria produto + cria stock_item
 *   - Produto existe, sem estoque → cria stock_item (preenche lacuna)
 *   - Produto existe, já tem estoque → ignora (sem duplicação)
 */
class InventoryDurableSyncService
{
    public function sync(int $uploadId): array
    {
        $upload = InventoryUpload::findOrFail($uploadId);

        $durableItems = DurableGoodsInventory::where('inventory_upload_id', $uploadId)->get();

        $stats = [
            'total_items'      => $durableItems->count(),
            'total_unique'     => 0,   // produtos únicos após agrupamento por código
            'created'          => 0,   // novo produto + novo estoque
            'stock_gap_filled' => 0,   // produto já existia mas sem estoque → estoque adicionado
            'qty_adjusted'     => 0,   // produto + estoque existiam mas qtd estava abaixo do inventário
            'qty_added'        => 0,   // total de unidades acrescentadas por ajuste de quantidade
            'qty_surplus'      => 0,   // itens cujo estoque está acima do inventário (não alterado)
            'in_sync'          => 0,   // produto + estoque já batiam exatamente
            'skipped_zero'     => 0,   // quantidade zero no inventário
            'errors'           => 0,
            'error_messages'   => [],
        ];

        if ($durableItems->isEmpty()) {
            return $stats;
        }

        // ── Agrupar por material_code (mesmo produto pode aparecer em múltiplas linhas do PDF)
        $grouped = $durableItems->groupBy('material_code')->map(function ($items) {
            $first = $items->first();
            return (object) [
                'material_code'    => $first->material_code,
                'material_name'    => $first->material_name,
                'quantity'         => $items->sum('quantity'),  // soma todos os lotes do PDF
                'unit_value'       => $first->unit_value,
                'accounting_account' => $first->accounting_account,
            ];
        })->values();

        $stats['total_unique'] = $grouped->count();

        $category = Category::firstOrCreate(
            ['name' => 'Uso Duráduro'],
            ['description' => 'Materiais de uso duráduro importados do SISCOFIS']
        );

        DB::transaction(function () use ($grouped, $category, $upload, &$stats) {
            foreach ($grouped as $item) {
                try {
                    if ($item->quantity <= 0) {
                        $stats['skipped_zero']++;
                        continue;
                    }

                    // ── 1. Produto ────────────────────────────────────────────
                    $product = Product::where('siscofis_code', $item->material_code)->first();
                    $isNewProduct = false;

                    if (!$product) {
                        $product = Product::create([
                            'name'              => $item->material_name,
                            'category_id'       => $category->id,
                            'siscofis_code'     => $item->material_code,
                            'is_durable'        => true,
                            'shelf_life_months' => null,
                            'minimum_stock'     => 0,
                            'is_serialized'     => false,
                        ]);
                        $isNewProduct = true;
                    } elseif (!$product->is_durable) {
                        $product->update(['is_durable' => true]);
                    }

                    // ── 2. Estoque ────────────────────────────────────────────
                    $currentQty = StockItem::where('product_id', $product->id)->sum('quantity');

                    if ($currentQty == 0) {
                        // Nenhum estoque: criar entrada inicial
                        $stockItem = StockItem::create([
                            'product_id'      => $product->id,
                            'quantity'        => $item->quantity,
                            'batch'           => 'INV-' . $upload->id,
                            'location'        => $upload->dependency ?? 'Inventário SISCOFIS',
                            'unit_cost'       => $item->unit_value,
                            'status'          => 'available',
                            'expiration_date' => null,
                            'serial_number'   => null,
                        ]);

                        StockMovement::create([
                            'stock_item_id' => $stockItem->id,
                            'movement_type' => 'entry',
                            'quantity'      => $item->quantity,
                            'performed_by'  => $upload->uploaded_by,
                            'notes'         => 'Entrada do inventário SISCOFIS - Upload #' . $upload->id . ' (' . $upload->filename . ')'
                                . ($isNewProduct ? '' : ' [produto já existia, estoque faltava]'),
                        ]);

                        if ($isNewProduct) {
                            $stats['created']++;
                        } else {
                            $stats['stock_gap_filled']++;
                        }
                        continue;
                    }

                    // Já tem estoque — reconciliar quantidades
                    $diff = $item->quantity - $currentQty;

                    if ($diff > 0) {
                        // Inventário tem mais que o estoque: adicionar a diferença
                        $stockItem = StockItem::create([
                            'product_id'      => $product->id,
                            'quantity'        => $diff,
                            'batch'           => 'INV-CORR-' . $upload->id,
                            'location'        => $upload->dependency ?? 'Inventário SISCOFIS',
                            'unit_cost'       => $item->unit_value,
                            'status'          => 'available',
                            'expiration_date' => null,
                            'serial_number'   => null,
                        ]);

                        StockMovement::create([
                            'stock_item_id' => $stockItem->id,
                            'movement_type' => 'entry',
                            'quantity'      => $diff,
                            'performed_by'  => $upload->uploaded_by,
                            'notes'         => "Ajuste de reconciliação SISCOFIS: inventário={$item->quantity} estoque={$currentQty} diferença=+{$diff} (Upload #{$upload->id})",
                        ]);

                        $stats['qty_adjusted']++;
                        $stats['qty_added'] += $diff;

                    } elseif ($diff < 0) {
                        // Estoque maior que inventário — não reduzir (pode haver cautelas abertas)
                        $stats['qty_surplus']++;

                    } else {
                        // Já está em sincronia
                        $stats['in_sync']++;
                    }

                } catch (\Exception $e) {
                    $stats['errors']++;
                    $stats['error_messages'][] = "Item {$item->material_code}: {$e->getMessage()}";
                    Log::error("Erro ao sincronizar item durável {$item->material_code}: {$e->getMessage()}");
                }
            }
        });

        Log::info("Sync de duráveis concluído para upload #{$uploadId}", $stats);
        return $stats;
    }
}
