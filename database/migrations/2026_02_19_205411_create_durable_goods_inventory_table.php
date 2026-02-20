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
        Schema::create('durable_goods_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_upload_id')->constrained('inventory_uploads')->cascadeOnDelete();
            $table->string('material_name');
            $table->string('ficha_number')->nullable();
            $table->string('material_code')->nullable()->index();
            $table->string('accounting_account')->nullable();
            $table->integer('quantity')->default(0);
            $table->decimal('unit_value', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->text('raw_text')->nullable();
            $table->timestamps();

            // Índices para buscas
            $table->index('material_name');
            $table->index('ficha_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('durable_goods_inventory');
    }
};
