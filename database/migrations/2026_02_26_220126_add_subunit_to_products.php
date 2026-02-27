<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('subunit')->nullable()->after('id');
        });

        // Backfill: todos os produtos existentes pertencem ao admin (1ª Cia)
        DB::statement("UPDATE products SET subunit = '1ª Cia' WHERE subunit IS NULL");

        // Fix stock_items cujo subunit ficou como string vazia
        DB::statement("UPDATE stock_items SET subunit = '1ª Cia' WHERE subunit = '' OR subunit IS NULL");
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('subunit');
        });
    }
};
