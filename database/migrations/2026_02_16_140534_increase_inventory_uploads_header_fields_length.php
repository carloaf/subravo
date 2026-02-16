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
        Schema::table('inventory_uploads', function (Blueprint $table) {
            $table->string('dependency', 500)->nullable()->change();
            $table->string('unit', 500)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_uploads', function (Blueprint $table) {
            $table->string('dependency', 255)->nullable()->change();
            $table->string('unit', 255)->nullable()->change();
        });
    }
};
