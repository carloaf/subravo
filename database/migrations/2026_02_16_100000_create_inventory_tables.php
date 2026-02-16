<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Uploads de inventário (cada PDF carregado) ────────────
        Schema::create('inventory_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('filename');                          // nome original do arquivo
            $table->string('stored_path');                       // caminho em storage/inventario
            $table->string('dependency')->nullable();            // ex: "11o D Sup"
            $table->string('unit')->nullable();                  // ex: "1a CIA SUP"
            $table->string('unit_code')->nullable();             // ex: "37"
            $table->foreignId('uploaded_by')->constrained('users');
            $table->enum('status', ['pending', 'processing', 'completed', 'error'])->default('pending');
            $table->integer('total_items')->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        // ── Itens do inventário (cada material extraído do PDF) ──
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_upload_id')->constrained('inventory_uploads')->cascadeOnDelete();
            $table->string('material_type')->nullable();         // "MATERIAL PERMANENTE", etc.
            $table->string('material_name');                     // nome do material
            $table->string('ficha_number')->nullable();          // Nr Ficha
            $table->string('material_code')->nullable();         // Cod Mat
            $table->string('accounting_account')->nullable();    // Conta Contábil
            $table->string('acervo')->nullable();                // N ou S
            $table->integer('quantity')->default(1);
            $table->decimal('unit_value', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->json('patrimony_numbers')->nullable();       // array de nrs patrimoniais
            $table->text('raw_text')->nullable();                // texto bruto extraído (debug)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('inventory_uploads');
    }
};
