<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('serial_number')->nullable()->unique()->comment('Nº de série');
            $table->string('batch')->nullable()->comment('Lote');
            $table->date('expiration_date')->nullable()->comment('Validade');
            $table->date('siscofis_entry_date')->comment('Data entrada SISCOFIS');
            $table->string('location')->comment('Prateleira/corredor/armário');
            $table->string('subunit')->nullable()->comment('Subunidade responsável');
            $table->integer('quantity')->default(1)->comment('Qtd para itens não serializados');
            $table->enum('status', ['available', 'loaned', 'damaged', 'decommissioned'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('expiration_date');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_items');
    }
};
