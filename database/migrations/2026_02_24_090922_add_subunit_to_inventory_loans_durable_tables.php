<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona coluna `subunit` em inventory_uploads, loans e durable_goods_inventory
     * para isolar dados por subunidade. Máx. 2 usuários por subunidade.
     */
    public function up(): void
    {
        Schema::table('inventory_uploads', function (Blueprint $table) {
            $table->string('subunit')->nullable()->after('uploaded_by')
                  ->comment('Subunidade responsável pelo inventário');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->string('subunit')->nullable()->after('loaned_by')
                  ->comment('Subunidade que emitiu a cautela');
        });

        Schema::table('durable_goods_inventory', function (Blueprint $table) {
            $table->string('subunit')->nullable()->after('id')
                  ->comment('Subunidade responsável pelo bem duradouro');
        });

        // Propagar subunit dos usuários para registros já existentes
        DB::statement("
            UPDATE inventory_uploads iu
            SET subunit = u.subunit
            FROM users u
            WHERE iu.uploaded_by = u.id
              AND u.subunit IS NOT NULL
              AND iu.subunit IS NULL
        ");

        DB::statement("
            UPDATE loans l
            SET subunit = u.subunit
            FROM users u
            WHERE l.loaned_by = u.id
              AND u.subunit IS NOT NULL
              AND l.subunit IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('inventory_uploads', function (Blueprint $table) {
            $table->dropColumn('subunit');
        });
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('subunit');
        });
        Schema::table('durable_goods_inventory', function (Blueprint $table) {
            $table->dropColumn('subunit');
        });
    }
};
