<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Uploads de inventário (cada PDF carregado) ───────────
        Schema::create('inventory_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('stored_path');
            $table->string('dependency', 500)->nullable();
            $table->string('unit', 500)->nullable();
            $table->string('unit_code')->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->string('subunit')->nullable()->comment('Subunidade responsável pelo inventário');
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
            $table->string('material_type')->nullable()->index();
            $table->text('material_name')->nullable();
            $table->string('ficha_number', 500)->nullable();
            $table->string('material_code', 500)->nullable();
            $table->string('accounting_account')->nullable();
            $table->string('acervo')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_value', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->json('patrimony_numbers')->nullable();
            $table->text('raw_text')->nullable();
            $table->timestamps();
        });

        // Índices GIN para busca full-text com trigram
        DB::statement('CREATE INDEX IF NOT EXISTS inventory_items_material_name_trgm_idx ON inventory_items USING gin (material_name gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS inventory_items_material_code_trgm_idx ON inventory_items USING gin (material_code gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS inventory_items_ficha_number_trgm_idx ON inventory_items USING gin (ficha_number gin_trgm_ops)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS inventory_items_material_name_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS inventory_items_material_code_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS inventory_items_ficha_number_trgm_idx');

        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('inventory_uploads');
    }
};
