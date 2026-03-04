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

        $inventoryItems = DurableGoodsInventory::where('inventory_upload_id', $uploadId)->get();

        $stats = [
            'total_items'      => $inventoryItems->count(), // linhas brutas no durable_goods_inventory
            'total_unique'     => 0,   // produtos únicos pelo código SISCOFIS
            'total_lots'       => 0,   // lotes únicos após agregação de fichas duplicadas
            'lots_created'     => 0,   // novos lotes de estoque criados
            'created'          => 0,   // novo produto + novo lote
            'stock_gap_filled' => 0,   // lote novo para produto já existente
            'qty_adjusted'     => 0,   // lote existente com qtd abaixo do inventário → ajustado
            'qty_added'        => 0,   // total de unidades acrescentadas por ajuste
            'qty_surplus'      => 0,   // lote cujo estoque está acima do inventário (não alterado)
            'in_sync'          => 0,   // lote já em sincronia
            'skipped_zero'     => 0,   // quantidade zero no inventário
            'errors'           => 0,
            'error_messages'   => [],
        ];

        if ($inventoryItems->isEmpty()) {
            return $stats;
        }

        // Contar produtos únicos (por código SISCOFIS)
        $stats['total_unique'] = $inventoryItems->pluck('material_code')->filter()->unique()->count();

        // ── Agregar linhas com o mesmo ficha_number ──────────────────────────
        // O mesmo nº de ficha pode aparecer N vezes na planilha SISCOFIS quando
        // o lote contém sub-itens (tamanhos, cores…). A quantidade correta é a
        // SOMA de todas as linhas do mesmo ficha.  Linhas sem ficha_number são
        // mantidas individualmente com chave INV-{uploadId}-{n}.
        $lotCounter = 0;
        $aggregated = $inventoryItems->groupBy(function ($item) use ($uploadId, &$lotCounter) {
            if ($item->ficha_number) {
                return 'FICHA-' . $item->ficha_number;
            }
            return 'INV-' . $uploadId . '-' . (++$lotCounter);
        })->map(function ($rows, $batchKey) {
            $first = $rows->first();
            return (object) [
                'batch_key'         => $batchKey,
                'material_code'     => $first->material_code,
                'material_name'     => $first->material_name,
                'quantity'          => $rows->sum('quantity'),   // ← soma correta
                'unit_value'        => $rows->max('unit_value'), // valor máximo do lote
                'accounting_account'=> $first->accounting_account,
                'row_count'         => $rows->count(),
            ];
        })->values();

        $stats['total_items'] = $inventoryItems->count();  // linhas brutas
        $stats['total_lots']  = $aggregated->count();      // lotes únicos após agregação

        $category = Category::firstOrCreate(
            ['name' => 'Uso Duráduro'],
            ['description' => 'Materiais de uso duráduro importados do SISCOFIS']
        );

        DB::transaction(function () use ($aggregated, $uploadId, $category, $upload, &$stats) {
            foreach ($aggregated as $lot) {
                try {
                    if ($lot->quantity <= 0) {
                        $stats['skipped_zero']++;
                        continue;
                    }

                    $batchKey = $lot->batch_key;

                    // ── 1. Produto ────────────────────────────────────────────
                    $product = Product::where('siscofis_code', $lot->material_code)->first();
                    $isNewProduct = false;

                    if (!$product) {
                        $product = Product::create([
                            'name'              => $lot->material_name,
                            'category_id'       => $category->id,
                            'siscofis_code'     => $lot->material_code,
                            'subunit'           => $upload->subunit,
                            'is_durable'        => true,
                            'shelf_life_months' => null,
                            'minimum_stock'     => 0,
                            'is_serialized'     => false,
                        ]);
                        $isNewProduct = true;
                    } elseif (!$product->is_durable) {
                        $product->update(['is_durable' => true]);
                    }

                    // ── 2. Lote de estoque ────────────────────────────────────
                    $existingLotItem = StockItem::where('product_id', $product->id)
                        ->where('batch', $batchKey)
                        ->first();

                    if (!$existingLotItem) {
                        $stockItem = StockItem::create([
                            'product_id'      => $product->id,
                            'quantity'        => $lot->quantity,
                            'batch'           => $batchKey,
                            'location'        => $upload->dependency ?? 'Inventário SISCOFIS',
                            'subunit'         => $upload->subunit,
                            'unit_cost'       => $lot->unit_value,
                            'status'          => 'available',
                            'expiration_date' => null,
                            'serial_number'   => null,
                        ]);

                        $note = 'Entrada do inventário SISCOFIS (lote ' . $batchKey . ') - Upload #' . $upload->id
                            . ' (' . $upload->filename . ')';
                        if ($lot->row_count > 1) {
                            $note .= " [{$lot->row_count} sub-itens agregados]";
                        }
                        if (!$isNewProduct) {
                            $note .= ' [produto já existia]';
                        }

                        StockMovement::create([
                            'stock_item_id' => $stockItem->id,
                            'movement_type' => 'entry',
                            'quantity'      => $lot->quantity,
                            'performed_by'  => $upload->uploaded_by,
                            'notes'         => $note,
                        ]);

                        $stats['lots_created']++;
                        $isNewProduct ? $stats['created']++ : $stats['stock_gap_filled']++;
                        continue;
                    }

                    // Lote já existe — atualizar custo e reconciliar quantidade
                    $updates = [];
                    if ((float) $existingLotItem->unit_cost !== (float) $lot->unit_value) {
                        $updates['unit_cost'] = $lot->unit_value;
                    }
                    if ($updates) {
                        $existingLotItem->update($updates);
                    }

                    $diff = $lot->quantity - $existingLotItem->quantity;

                    if ($diff > 0) {
                        $existingLotItem->increment('quantity', $diff);

                        StockMovement::create([
                            'stock_item_id' => $existingLotItem->id,
                            'movement_type' => 'entry',
                            'quantity'      => $diff,
                            'performed_by'  => $upload->uploaded_by,
                            'notes'         => "Ajuste SISCOFIS (lote {$batchKey}): "
                                . "inventário={$lot->quantity} estoque=" . ($existingLotItem->quantity - $diff)
                                . " +{$diff} (Upload #{$uploadId})"
                                . ($lot->row_count > 1 ? " [{$lot->row_count} sub-itens]" : ''),
                        ]);

                        $stats['qty_adjusted']++;
                        $stats['qty_added'] += $diff;
                    } elseif ($diff < 0) {
                        $stats['qty_surplus']++;
                    } else {
                        $stats['in_sync']++;
                    }

                } catch (\Exception $e) {
                    $stats['errors']++;
                    $stats['error_messages'][] = "Lote {$lot->batch_key} ({$lot->material_code}): {$e->getMessage()}";
                    Log::error("Erro ao sincronizar lote {$lot->batch_key} {$lot->material_code}: {$e->getMessage()}");
                }
            }
        });

        Log::info("Sync de duráveis concluído para upload #{$uploadId}", $stats);
        return $stats;
    }
}
