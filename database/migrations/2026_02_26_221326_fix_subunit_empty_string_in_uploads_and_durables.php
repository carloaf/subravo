<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PDFs carregados pelo admin (000000000) antes da coluna subunit existir
        // ficaram com subunit = '' → corrigir para '1ª Cia'
        DB::statement("UPDATE inventory_uploads SET subunit = '1ª Cia' WHERE subunit = '' OR subunit IS NULL");
        DB::statement("UPDATE durable_goods_inventory SET subunit = '1ª Cia' WHERE subunit = '' OR subunit IS NULL");
    }

    public function down(): void
    {
        // Não é possível desfazer com segurança sem saber o valor original
    }
};
