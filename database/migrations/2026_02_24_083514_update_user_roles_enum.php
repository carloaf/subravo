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
        // Dropar a constraint primeiro
        DB::statement("ALTER TABLE users DROP CONSTRAINT users_role_check");

        // Mapear os roles existentes para os novos
        DB::statement("UPDATE users SET role = 'user' WHERE role IN ('solicitante', 'almoxarife', 'auditor')");

        // Recriar a constraint com os novos valores permitidos
        DB::statement("
            ALTER TABLE users
            ADD CONSTRAINT users_role_check
            CHECK (role::text = ANY (ARRAY['admin'::character varying::text, 'manager'::character varying::text, 'user'::character varying::text]))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter para os valores originais
        DB::statement("
            ALTER TABLE users
            DROP CONSTRAINT users_role_check,
            ADD CONSTRAINT users_role_check
            CHECK (role::text = ANY (ARRAY['admin'::character varying::text, 'almoxarife'::character varying::text, 'solicitante'::character varying::text, 'auditor'::character varying::text]))
        ");

        // Reverter os roles
        DB::statement("UPDATE users SET role = 'solicitante' WHERE role = 'user'");
    }
};
