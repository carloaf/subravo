<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->string('borrower_cpf', 14)->nullable()->after('borrower_organization_id');
            $table->string('borrower_phone', 20)->nullable()->after('borrower_cpf');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['borrower_cpf', 'borrower_phone']);
        });
    }
};
