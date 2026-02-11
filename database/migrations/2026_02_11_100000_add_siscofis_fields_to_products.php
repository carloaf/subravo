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
        Schema::table('products', function (Blueprint $table) {
            $table->string('siscofis_code')->nullable()->after('name')->comment('Código/Ficha SISCOFIS do material');
            $table->integer('shelf_life_months')->nullable()->after('is_serialized')->comment('Validade em meses após entrada SISCOFIS');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['siscofis_code', 'shelf_life_months']);
        });
    }
};
