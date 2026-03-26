<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RankSeeder extends Seeder
{
    public function run(): void
    {
        $ranks = [
            ['name' => 'General de Exército',  'abbreviation' => 'Gen Ex',   'order' => 1],
            ['name' => 'General de Divisão',    'abbreviation' => 'Gen Div',  'order' => 2],
            ['name' => 'General de Brigada',    'abbreviation' => 'Gen Bda',  'order' => 3],
            ['name' => 'Coronel',               'abbreviation' => 'Cel',      'order' => 4],
            ['name' => 'Tenente-Coronel',       'abbreviation' => 'TC',       'order' => 5],
            ['name' => 'Major',                 'abbreviation' => 'Maj',      'order' => 6],
            ['name' => 'Capitão',               'abbreviation' => 'Cap',      'order' => 7],
            ['name' => '1º Tenente',            'abbreviation' => '1º Ten',   'order' => 8],
            ['name' => '2º Tenente',            'abbreviation' => '2º Ten',   'order' => 9],
            ['name' => 'Aspirante a Oficial',   'abbreviation' => 'Asp',      'order' => 10],
            ['name' => 'Subtenente',            'abbreviation' => 'ST',       'order' => 11],
            ['name' => '1º Sargento',           'abbreviation' => '1º Sgt',   'order' => 12],
            ['name' => '2º Sargento',           'abbreviation' => '2º Sgt',   'order' => 13],
            ['name' => '3º Sargento',           'abbreviation' => '3º Sgt',   'order' => 14],
            ['name' => 'Cabo',                  'abbreviation' => 'Cb',       'order' => 15],
            ['name' => 'Soldado',               'abbreviation' => 'Sd',       'order' => 16],
        ];

        $now = now();

        foreach ($ranks as &$rank) {
            $rank['created_at'] = $now;
            $rank['updated_at'] = $now;
        }

        DB::table('ranks')->upsert(
            $ranks,
            ['name'],
            ['abbreviation', 'order', 'updated_at']
        );
    }
}
