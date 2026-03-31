<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('irdu_items', function (Blueprint $table) {
            $table->id();
            $table->char('annex', 1)->comment('Anexo IRDU (A, B, C, D, E)');
            $table->string('annex_title')->comment('Título do anexo');
            $table->integer('item_number')->comment('Nº do item no anexo');
            $table->string('material_name')->comment('Nome do material');
            $table->string('duration_text')->comment('Duração original em texto (ex: 2 Anos)');
            $table->integer('duration_months')->nullable()->comment('Duração em meses (null = indeterminado)');
            $table->json('dotacoes')->comment('Detalhamento de todas as dotações');
            $table->timestamps();

            $table->unique(['annex', 'item_number']);
            $table->index('material_name');
            $table->index('duration_months');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('irdu_items');
    }
};
