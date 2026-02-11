<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Fardamento',              'description' => 'Uniformes, gandolas, calças, camisetas, bonés, coturnos e demais peças de fardamento.'],
            ['name' => 'Equipamento Individual',   'description' => 'Mochilas, cantis, marmitas, talheres, mosquetões e equipamentos de uso pessoal.'],
            ['name' => 'Material de Camping',      'description' => 'Barracas, sacos de dormir, colchonetes, lonas e acessórios de bivaque.'],
            ['name' => 'Cama e Mesa',              'description' => 'Lençóis, cobertores, fronhas, toalhas de mesa e roupa de cama.'],
            ['name' => 'Cozinha',                  'description' => 'Panelas, talheres industriais, bandejas, canecas e utensílios de rancho.'],
            ['name' => 'Ferramentas',              'description' => 'Ferramentas manuais, elétricas e de manutenção em geral.'],
            ['name' => 'Material de Expediente',   'description' => 'Papel, canetas, pastas, grampeadores, cartuchos e material de escritório.'],
            ['name' => 'Outros',                   'description' => 'Materiais diversos não classificados nas demais categorias.'],
        ];

        $now = now();

        foreach ($categories as &$cat) {
            $cat['created_at'] = $now;
            $cat['updated_at'] = $now;
        }

        DB::table('categories')->insert($categories);
    }
}
