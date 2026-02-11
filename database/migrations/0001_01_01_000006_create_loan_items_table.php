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
        Schema::create('loan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('stock_item_id')->constrained('stock_items')->cascadeOnUpdate()->restrictOnDelete();
            $table->integer('quantity')->default(1);
            $table->integer('returned_quantity')->default(0);
            $table->string('condition_out')->comment('Estado na saída');
            $table->string('condition_in')->nullable()->comment('Estado na devolução');
            $table->timestamps();

            $table->index('loan_id');
            $table->index('stock_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_items');
    }
};
