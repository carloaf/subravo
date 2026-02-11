<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = [
            ['name' => '11º Depósito de Suprimento',    'abbreviation' => '11º D Sup',  'is_host' => true],
            ['name' => '1º Batalhão de Infantaria',      'abbreviation' => '1º BI',      'is_host' => false],
            ['name' => '2º Batalhão de Infantaria',      'abbreviation' => '2º BI',      'is_host' => false],
            ['name' => '3º Batalhão de Infantaria',      'abbreviation' => '3º BI',      'is_host' => false],
            ['name' => '1º Regimento de Cavalaria',      'abbreviation' => '1º RC',      'is_host' => false],
            ['name' => '2º Regimento de Cavalaria',      'abbreviation' => '2º RC',      'is_host' => false],
            ['name' => '1º Grupo de Artilharia',         'abbreviation' => '1º GAC',     'is_host' => false],
            ['name' => '2º Grupo de Artilharia',         'abbreviation' => '2º GAC',     'is_host' => false],
            ['name' => 'Batalhão de Engenharia',         'abbreviation' => 'BE',         'is_host' => false],
            ['name' => 'Batalhão de Comunicações',       'abbreviation' => 'B Com',      'is_host' => false],
            ['name' => 'Batalhão Logístico',             'abbreviation' => 'B Log',      'is_host' => false],
            ['name' => 'Hospital Militar',               'abbreviation' => 'HM',         'is_host' => false],
            ['name' => 'Academia Militar',               'abbreviation' => 'AM',         'is_host' => false],
        ];

        $now = now();

        foreach ($organizations as &$org) {
            $org['created_at'] = $now;
            $org['updated_at'] = $now;
        }

        DB::table('organizations')->insert($organizations);
    }
}
