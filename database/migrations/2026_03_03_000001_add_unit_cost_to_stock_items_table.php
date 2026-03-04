<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona custo unitário ao item de estoque para suportar lotes com preços distintos.
     */
    public function up(): void
    {
        Schema::table('stock_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 15, 2)->nullable()->after('quantity')
                ->comment('Custo unitário do lote (R$)');
        });
    }

    public function down(): void
    {
        Schema::table('stock_items', function (Blueprint $table) {
            $table->dropColumn('unit_cost');
        });
    }
};
