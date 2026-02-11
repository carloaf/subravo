<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Corrige colunas stock_items: siscofis_entry_date e location devem ser nullable
     * para alinhar com a validação do controller (ambas são optional).
     */
    public function up(): void
    {
        Schema::table('stock_items', function (Blueprint $table) {
            $table->date('siscofis_entry_date')->nullable()->comment('Data entrada SISCOFIS')->change();
            $table->string('location')->nullable()->comment('Prateleira/corredor/armário')->change();
        });
    }

    public function down(): void
    {
        Schema::table('stock_items', function (Blueprint $table) {
            $table->date('siscofis_entry_date')->nullable(false)->comment('Data entrada SISCOFIS')->change();
            $table->string('location')->nullable(false)->comment('Prateleira/corredor/armário')->change();
        });
    }
};
