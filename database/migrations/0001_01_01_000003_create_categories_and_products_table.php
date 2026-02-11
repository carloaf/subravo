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
        // -----------------------------------------------------------------
        // categories — Categorias de Material
        // -----------------------------------------------------------------
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // -----------------------------------------------------------------
        // products — Produtos / Material de Intendência
        // -----------------------------------------------------------------
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('unit')->default('un')->comment('un, pç, par, kg, etc.');
            $table->integer('minimum_stock')->default(0)->comment('Alerta de estoque baixo');
            $table->boolean('is_serialized')->default(false)->comment('Controle por nº de série');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
