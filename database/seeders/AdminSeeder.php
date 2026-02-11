<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Busca a OM host (11º D Sup) e o posto de Coronel como padrão
        $organizationId = DB::table('organizations')->where('is_host', true)->value('id') ?? 1;
        $rankId = DB::table('ranks')->where('abbreviation', 'Cel')->value('id') ?? 4;

        DB::table('users')->insert([
            'identity_number'  => '000000000',
            'password'         => Hash::make('subravo2026'),
            'full_name'        => 'Administrador do Sistema',
            'war_name'         => 'ADMIN',
            'email'            => 'admin@subravo.local',
            'rank_id'          => $rankId,
            'organization_id'  => $organizationId,
            'subunit'          => null,
            'armed_force'      => 'EB',
            'gender'           => 'M',
            'role'             => 'admin',
            'is_active'        => true,
            'avatar_url'       => null,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }
}
