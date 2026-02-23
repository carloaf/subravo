<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Habilitar extensão pg_trgm para suporte a busca full-text com trigram
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Desabilitar extensão pg_trgm
        DB::statement('DROP EXTENSION IF EXISTS pg_trgm');
    }
};
