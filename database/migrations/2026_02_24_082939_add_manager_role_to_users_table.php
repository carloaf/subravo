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
        // Remover a constraint existente
        DB::statement("ALTER TABLE users DROP CONSTRAINT users_role_check");

        // Adicionar nova constraint com 'manager' incluído
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role::text = ANY (ARRAY['admin'::character varying::text, 'almoxarife'::character varying::text, 'solicitante'::character varying::text, 'auditor'::character varying::text, 'manager'::character varying::text]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter para a constraint original (sem 'manager')
        DB::statement("ALTER TABLE users DROP CONSTRAINT users_role_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role::text = ANY (ARRAY['admin'::character varying::text, 'almoxarife'::character varying::text, 'solicitante'::character varying::text, 'auditor'::character varying::text]))");
    }
};
