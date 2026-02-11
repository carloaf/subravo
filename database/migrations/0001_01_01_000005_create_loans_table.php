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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_number')->unique()->comment('CAUTELA-2026-000001');
            $table->enum('borrower_type', ['individual', 'section']);
            $table->foreignId('borrower_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->string('borrower_section')->nullable()->comment('Seção/subunidade quando type=section');
            $table->foreignId('borrower_organization_id')->nullable()->constrained('organizations')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('loaned_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete()->comment('Almoxarife que emprestou');
            $table->datetime('loan_date');
            $table->date('expected_return_date')->nullable();
            $table->datetime('actual_return_date')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete()->comment('Quem recebeu devolução');
            $table->enum('status', ['active', 'returned', 'partial', 'overdue'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('loan_date');
            $table->index('borrower_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
