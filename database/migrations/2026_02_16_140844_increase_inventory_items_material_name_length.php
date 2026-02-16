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
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->text('material_name')->change();
            $table->string('ficha_number', 500)->nullable()->change();
            $table->string('material_code', 500)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->string('material_name', 255)->change();
            $table->string('ficha_number', 255)->nullable()->change();
            $table->string('material_code', 255)->nullable()->change();
        });
    }
};
