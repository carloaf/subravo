<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nomes de material podem ultrapassar 255 chars (descrições longas do SISCOFIS).
     * Converter para text nas duas tabelas.
     */
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->text('material_name')->nullable()->change();
        });

        Schema::table('durable_goods_inventory', function (Blueprint $table) {
            $table->text('material_name')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->string('material_name', 255)->nullable()->change();
        });

        Schema::table('durable_goods_inventory', function (Blueprint $table) {
            $table->string('material_name', 255)->nullable()->change();
        });
    }
};
