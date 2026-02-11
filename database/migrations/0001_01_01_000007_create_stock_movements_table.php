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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_item_id')->constrained('stock_items')->cascadeOnUpdate()->restrictOnDelete();
            $table->enum('movement_type', ['entry', 'exit', 'loan', 'return', 'adjustment', 'decommission']);
            $table->integer('quantity');
            $table->string('reference_type')->nullable()->comment('loan, manual, etc.');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('loan_id, etc.');
            $table->foreignId('performed_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('stock_item_id');
            $table->index('movement_type');
            $table->index('performed_by');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
