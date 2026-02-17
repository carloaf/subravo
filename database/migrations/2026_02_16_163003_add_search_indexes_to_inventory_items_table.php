<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Criar índices GIN para busca full-text e trigram
        DB::statement('CREATE INDEX IF NOT EXISTS inventory_items_material_name_trgm_idx ON inventory_items USING gin (material_name gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS inventory_items_material_code_trgm_idx ON inventory_items USING gin (material_code gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS inventory_items_ficha_number_trgm_idx ON inventory_items USING gin (ficha_number gin_trgm_ops)');
        
        // Índices adicionais para performance
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->index('material_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover índices GIN
        DB::statement('DROP INDEX IF EXISTS inventory_items_material_name_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS inventory_items_material_code_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS inventory_items_ficha_number_trgm_idx');
        
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropIndex(['material_type']);
        });
    }
};
