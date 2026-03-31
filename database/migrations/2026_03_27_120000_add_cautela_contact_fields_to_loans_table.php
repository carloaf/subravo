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
        Schema::table('loans', function (Blueprint $table) {
            $table->string('borrower_identity_number', 30)->nullable()->after('borrower_phone');
            $table->string('borrower_rank', 50)->nullable()->after('borrower_identity_number');
            $table->string('borrower_war_name', 100)->nullable()->after('borrower_rank');
            $table->string('signer_name')->nullable()->after('borrower_identity_number');
            $table->string('signer_rank', 50)->nullable()->after('signer_name');
            $table->string('signer_war_name', 100)->nullable()->after('signer_rank');
            $table->string('signer_identity_number', 30)->nullable()->after('signer_name');
            $table->string('signer_cpf', 14)->nullable()->after('signer_identity_number');
            $table->string('signer_phone', 20)->nullable()->after('signer_cpf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn([
                'borrower_identity_number',
                'borrower_rank',
                'borrower_war_name',
                'signer_name',
                'signer_rank',
                'signer_war_name',
                'signer_identity_number',
                'signer_cpf',
                'signer_phone',
            ]);
        });
    }
};
