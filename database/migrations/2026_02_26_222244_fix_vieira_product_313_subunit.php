<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Produto #313 (GUARDANAPO) e StockItem #510 foram criados por VIEIRA (2ª Cia)
     * antes do fix no ProductController. A migration de backfill anterior atribuiu
     * incorretamente subunit='1ª Cia'. Esta migration corrige para '2ª Cia'.
     */
    public function up(): void
    {
        // Corrigir apenas se todos os movimentos são de usuários da 2ª Cia
        DB::statement("
            UPDATE products
            SET subunit = '2ª Cia'
            WHERE id = 313
              AND NOT EXISTS (
                SELECT 1 FROM stock_items si
                JOIN stock_movements sm ON sm.stock_item_id = si.id
                JOIN users u ON u.id = sm.performed_by
                WHERE si.product_id = products.id
                  AND (u.subunit IS NULL OR u.subunit != '2ª Cia')
              )
        ");

        DB::statement("
            UPDATE stock_items
            SET subunit = '2ª Cia'
            WHERE product_id = 313
              AND NOT EXISTS (
                SELECT 1 FROM stock_movements sm
                JOIN users u ON u.id = sm.performed_by
                WHERE sm.stock_item_id = stock_items.id
                  AND (u.subunit IS NULL OR u.subunit != '2ª Cia')
              )
        ");
    }

    public function down(): void
    {
        DB::statement("UPDATE products SET subunit = '1ª Cia' WHERE id = 313");
        DB::statement("UPDATE stock_items SET subunit = '1ª Cia' WHERE product_id = 313");
    }
};
