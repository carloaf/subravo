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
use Illuminate\Support\Facades\Auth;

/**
 * Sincroniza itens de "Material de Uso Duradouro" do inventário
 * com a tabela de produtos (products).
 */
class InventoryDurableSyncService
{
    /**
     * Sincronizar itens de uso duradouro de um upload com a tabela products.
     * 
     * @param int $uploadId ID do InventoryUpload
     * @return array Estatísticas da sincronização
     */
    public function sync(int $uploadId): array
    {
        $upload = InventoryUpload::findOrFail($uploadId);
        
        // Buscar todos os itens de Material de Uso Duradouro do upload
        $durableItems = DurableGoodsInventory::where('inventory_upload_id', $uploadId)->get();

        $stats = [
            'total_items'      => $durableItems->count(),
            'created'          => 0,
            'already_exists'   => 0,
            'stock_created'    => 0,
            'stock_skipped'    => 0,
            'errors'           => 0,
            'error_messages'   => [],
        ];

        if ($durableItems->isEmpty()) {
            Log::info("Nenhum item de uso duradouro encontrado no upload #{$uploadId}");
            return $stats;
        }

        // Buscar ou criar categoria "Uso Duradouro"
        $category = Category::firstOrCreate(
            ['name' => 'Uso Duradouro'],
            ['description' => 'Materiais de uso duradouro importados do SISCOFIS']
        );

        DB::transaction(function () use ($durableItems, $category, $upload, &$stats) {
            foreach ($durableItems as $item) {
                try {
                    // Verificar se produto já existe pelo código SISCOFIS
                    $existingProduct = Product::where('siscofis_code', $item->material_code)->first();
                    $product = null;

                    if ($existingProduct) {
                        // Produto já existe - apenas garantir que is_durable = true
                        if (!$existingProduct->is_durable) {
                            $existingProduct->update(['is_durable' => true]);
                            Log::info("Produto #{$existingProduct->id} marcado como durável: {$item->material_code}");
                        }
                        $product = $existingProduct;
                        $stats['already_exists']++;
                    } else {
                        // Criar novo produto
                        $product = Product::create([
                            'name'              => $item->material_name,
                            'category_id'       => $category->id,
                            'siscofis_code'     => $item->material_code,
                            'is_durable'        => true,
                            'shelf_life_months' => null,  // Duráveis não têm validade
                            'minimum_stock'     => 0,
                            'is_serialized'     => false,
                        ]);

                        $stats['created']++;
                        Log::info("Produto durável criado: {$product->name} (SISCOFIS: {$item->material_code})");
                    }

                    // Verificar se já existe entrada no estoque para este produto
                    $hasStockItems = StockItem::where('product_id', $product->id)->exists();

                    if (!$hasStockItems && $item->quantity > 0) {
                        // Criar entrada inicial no estoque baseada no inventário
                        $stockItem = StockItem::create([
                            'product_id'       => $product->id,
                            'quantity'         => $item->quantity,
                            'batch'            => 'INV-' . $upload->id,
                            'location'         => $upload->dependency ?? 'Inventário SISCOFIS',
                            'unit_cost'        => $item->unit_value,
                            'status'           => 'available',
                            'expiration_date'  => null, // Duráveis não expiram
                            'serial_number'    => null,
                        ]);

                        // Registrar movimentação
                        StockMovement::create([
                            'stock_item_id'  => $stockItem->id,
                            'movement_type'  => 'entry',
                            'quantity'       => $item->quantity,
                            'performed_by'   => $upload->uploaded_by,
                            'notes'          => "Entrada inicial automática do inventário SISCOFIS - Upload #{$upload->id} ({$upload->filename})",
                        ]);

                        $stats['stock_created']++;
                        Log::info("Estoque criado automaticamente para {$product->name}: {$item->quantity} unidades");
                    } else {
                        $stats['stock_skipped']++;
                    }

                } catch (\Exception $e) {
                    $stats['errors']++;
                    $stats['error_messages'][] = "Item {$item->material_code}: {$e->getMessage()}";
                    Log::error("Erro ao sincronizar item durável {$item->material_code}: {$e->getMessage()}");
                }
            }
        });

        Log::info("Sincronização de duráveis concluída para upload #{$uploadId}", $stats);
        return $stats;
    }
}
