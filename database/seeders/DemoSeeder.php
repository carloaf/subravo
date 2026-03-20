<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\Loan;
use App\Models\LoanItem;

class DemoSeeder extends Seeder
{
    /**
    * Dados de demonstracao realistas para o HelpSub.
     *
     * Roda APÓS o DatabaseSeeder (ranks, organizations, categories, admin).
     * Uso: php artisan db:seed --class=DemoSeeder
     */
    public function run(): void
    {
        $this->command->info('🔧 Criando dados de demonstração...');

        // ─── IDs de referência ───────────────────────────────────
        $catFardamento    = DB::table('categories')->where('name', 'Fardamento')->value('id');
        $catEquipamento   = DB::table('categories')->where('name', 'Equipamento Individual')->value('id');
        $catCamping       = DB::table('categories')->where('name', 'Material de Camping')->value('id');
        $catCamaMesa      = DB::table('categories')->where('name', 'Cama e Mesa')->value('id');
        $catCozinha        = DB::table('categories')->where('name', 'Cozinha')->value('id');
        $catFerramentas    = DB::table('categories')->where('name', 'Ferramentas')->value('id');
        $catExpediente     = DB::table('categories')->where('name', 'Material de Expediente')->value('id');
        $catOutros         = DB::table('categories')->where('name', 'Outros')->value('id');

        $orgHost  = DB::table('organizations')->where('is_host', true)->value('id');
        $org1BI   = DB::table('organizations')->where('abbreviation', '1º BI')->value('id');
        $org2BI   = DB::table('organizations')->where('abbreviation', '2º BI')->value('id');
        $orgBLog  = DB::table('organizations')->where('abbreviation', 'B Log')->value('id');

        $adminId = DB::table('users')->where('identity_number', '000000000')->value('id');

        // ═══════════════════════════════════════════════════════════
        //  1. USUÁRIOS DE DEMONSTRAÇÃO
        // ═══════════════════════════════════════════════════════════
        $this->command->info('  → Usuários...');

        $rankCap  = DB::table('ranks')->where('abbreviation', 'Cap')->value('id');
        $rank1Sgt = DB::table('ranks')->where('abbreviation', '1º Sgt')->value('id');
        $rank2Sgt = DB::table('ranks')->where('abbreviation', '2º Sgt')->value('id');
        $rank3Sgt = DB::table('ranks')->where('abbreviation', '3º Sgt')->value('id');
        $rankCb   = DB::table('ranks')->where('abbreviation', 'Cb')->value('id');
        $rankSd   = DB::table('ranks')->where('abbreviation', 'Sd')->value('id');

        $users = [];
        $usersData = [
            [
                'identity_number'  => '111111111',
                'full_name'        => 'Carlos Eduardo Ferreira',
                'war_name'         => 'FERREIRA',
                'rank_id'          => $rankCap,
                'organization_id'  => $orgHost,
                'subunit'          => 'Seção de Aprovisionamento',
                'role'             => 'almoxarife',
                'gender'           => 'M',
            ],
            [
                'identity_number'  => '222222222',
                'full_name'        => 'Ana Paula dos Santos',
                'war_name'         => 'SANTOS',
                'rank_id'          => $rank1Sgt,
                'organization_id'  => $orgHost,
                'subunit'          => 'Almoxarifado Central',
                'role'             => 'almoxarife',
                'gender'           => 'F',
            ],
            [
                'identity_number'  => '333333333',
                'full_name'        => 'Rafael Augusto Lima',
                'war_name'         => 'LIMA',
                'rank_id'          => $rank2Sgt,
                'organization_id'  => $org1BI,
                'subunit'          => '1ª Cia',
                'role'             => 'solicitante',
                'gender'           => 'M',
            ],
            [
                'identity_number'  => '444444444',
                'full_name'        => 'Marcos Vinícius Rocha',
                'war_name'         => 'ROCHA',
                'rank_id'          => $rank3Sgt,
                'organization_id'  => $org2BI,
                'subunit'          => '2ª Cia',
                'role'             => 'solicitante',
                'gender'           => 'M',
            ],
            [
                'identity_number'  => '555555555',
                'full_name'        => 'Juliana Cristina Moreira',
                'war_name'         => 'MOREIRA',
                'rank_id'          => $rankCap,
                'organization_id'  => $orgHost,
                'subunit'          => null,
                'role'             => 'auditor',
                'gender'           => 'F',
            ],
        ];

        $now = now();
        foreach ($usersData as $u) {
            $users[$u['identity_number']] = DB::table('users')->insertGetId([
                'identity_number'  => $u['identity_number'],
                'password'         => Hash::make('helpsub2026'),
                'full_name'        => $u['full_name'],
                'war_name'         => $u['war_name'],
                'email'            => strtolower($u['war_name']) . '@helpsub.local',
                'rank_id'          => $u['rank_id'],
                'organization_id'  => $u['organization_id'],
                'subunit'          => $u['subunit'],
                'armed_force'      => 'EB',
                'gender'           => $u['gender'],
                'role'             => $u['role'],
                'is_active'        => true,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        $almoxarifeId  = $users['111111111'];
        $almoxarife2Id = $users['222222222'];
        $solicitanteId = $users['333333333'];
        $solicitante2Id = $users['444444444'];

        // ═══════════════════════════════════════════════════════════
        //  2. PRODUTOS — Material de Intendência
        // ═══════════════════════════════════════════════════════════
        $this->command->info('  → Produtos...');

        $products = [];
        $productsData = [
            // ── Fardamento ──────────────────────────────────────────
            ['name' => 'Japona de Campanha PP',        'category_id' => $catFardamento,  'unit' => 'pç',  'minimum_stock' => 20, 'is_serialized' => false, 'description' => 'Japona de campanha camuflada, tamanho PP.'],
            ['name' => 'Japona de Campanha P',         'category_id' => $catFardamento,  'unit' => 'pç',  'minimum_stock' => 30, 'is_serialized' => false, 'description' => 'Japona de campanha camuflada, tamanho P.'],
            ['name' => 'Japona de Campanha M',         'category_id' => $catFardamento,  'unit' => 'pç',  'minimum_stock' => 50, 'is_serialized' => false, 'description' => 'Japona de campanha camuflada, tamanho M.'],
            ['name' => 'Japona de Campanha G',         'category_id' => $catFardamento,  'unit' => 'pç',  'minimum_stock' => 40, 'is_serialized' => false, 'description' => 'Japona de campanha camuflada, tamanho G.'],
            ['name' => 'Japona de Campanha GG',        'category_id' => $catFardamento,  'unit' => 'pç',  'minimum_stock' => 15, 'is_serialized' => false, 'description' => 'Japona de campanha camuflada, tamanho GG.'],
            ['name' => 'Gandola Camuflada M',          'category_id' => $catFardamento,  'unit' => 'pç',  'minimum_stock' => 40, 'is_serialized' => false, 'description' => 'Gandola camuflada padrão EB, tamanho M.'],
            ['name' => 'Calça Camuflada M',            'category_id' => $catFardamento,  'unit' => 'pç',  'minimum_stock' => 40, 'is_serialized' => false, 'description' => 'Calça camuflada padrão EB, tamanho M.'],
            ['name' => 'Coturno de Campanha nº 40',    'category_id' => $catFardamento,  'unit' => 'par', 'minimum_stock' => 25, 'is_serialized' => false, 'description' => 'Coturno de campanha preto, numeração 40.'],
            ['name' => 'Camiseta Camuflada M',         'category_id' => $catFardamento,  'unit' => 'pç',  'minimum_stock' => 60, 'is_serialized' => false, 'description' => 'Camiseta meia-manga camuflada, tamanho M.'],
            ['name' => 'Boné Camuflado',               'category_id' => $catFardamento,  'unit' => 'un',  'minimum_stock' => 30, 'is_serialized' => false, 'description' => 'Boné camuflado padrão EB.'],

            // ── Material de Camping ─────────────────────────────────
            ['name' => 'Toldo de Campanha',            'category_id' => $catCamping, 'unit' => 'un', 'minimum_stock' => 10, 'is_serialized' => true,  'description' => 'Toldo de campanha impermeável 4x4m, cor verde oliva. Material resistente para bivaque.'],
            ['name' => 'Barraca 2 Praças',             'category_id' => $catCamping, 'unit' => 'un', 'minimum_stock' => 15, 'is_serialized' => true,  'description' => 'Barraca para 2 praças, cor camuflada, com sobreteto.'],
            ['name' => 'Barraca 4 Praças',             'category_id' => $catCamping, 'unit' => 'un', 'minimum_stock' => 8,  'is_serialized' => true,  'description' => 'Barraca para 4 praças, cor camuflada, com sobreteto.'],
            ['name' => 'Saco de Dormir',               'category_id' => $catCamping, 'unit' => 'un', 'minimum_stock' => 30, 'is_serialized' => false, 'description' => 'Saco de dormir térmico, -5°C a +10°C.'],
            ['name' => 'Colchonete Isolante',          'category_id' => $catCamping, 'unit' => 'un', 'minimum_stock' => 40, 'is_serialized' => false, 'description' => 'Colchonete isolante térmico EVA, 180x55cm.'],
            ['name' => 'Rede de Selva',                'category_id' => $catCamping, 'unit' => 'un', 'minimum_stock' => 20, 'is_serialized' => false, 'description' => 'Rede de selva com mosquiteiro e sobreteto.'],
            ['name' => 'Lona Camuflada 3x5m',         'category_id' => $catCamping, 'unit' => 'un', 'minimum_stock' => 10, 'is_serialized' => false, 'description' => 'Lona camuflada impermeável 3x5m para bivaque.'],

            // ── Equipamento Individual ───────────────────────────────
            ['name' => 'Mochila de Campanha',          'category_id' => $catEquipamento, 'unit' => 'un', 'minimum_stock' => 30, 'is_serialized' => true,  'description' => 'Mochila de campanha 70L, camuflada.'],
            ['name' => 'Cantil com Capa',              'category_id' => $catEquipamento, 'unit' => 'un', 'minimum_stock' => 50, 'is_serialized' => false, 'description' => 'Cantil alumínio 900ml com capa camuflada.'],
            ['name' => 'Marmita Individual',           'category_id' => $catEquipamento, 'unit' => 'un', 'minimum_stock' => 50, 'is_serialized' => false, 'description' => 'Marmita de alumínio individual com tampa.'],
            ['name' => 'Talher de Campanha',           'category_id' => $catEquipamento, 'unit' => 'cj', 'minimum_stock' => 50, 'is_serialized' => false, 'description' => 'Conjunto faca/garfo/colher inox dobrável.'],
            ['name' => 'Bornal de Assalto',            'category_id' => $catEquipamento, 'unit' => 'un', 'minimum_stock' => 20, 'is_serialized' => false, 'description' => 'Bornal de assalto camuflado, fixação no cinturão.'],
            ['name' => 'Capacete M1',                  'category_id' => $catEquipamento, 'unit' => 'un', 'minimum_stock' => 25, 'is_serialized' => true,  'description' => 'Capacete de combate modelo M1 com jugular.'],

            // ── Cama e Mesa ─────────────────────────────────────────
            ['name' => 'Cobertor de Lã',               'category_id' => $catCamaMesa, 'unit' => 'un', 'minimum_stock' => 40, 'is_serialized' => false, 'description' => 'Cobertor de lã verde oliva, solteiro.'],
            ['name' => 'Lençol de Solteiro',           'category_id' => $catCamaMesa, 'unit' => 'un', 'minimum_stock' => 60, 'is_serialized' => false, 'description' => 'Lençol branco de algodão, solteiro.'],
            ['name' => 'Fronha Branca',                'category_id' => $catCamaMesa, 'unit' => 'un', 'minimum_stock' => 60, 'is_serialized' => false, 'description' => 'Fronha branca de algodão padrão.'],
            ['name' => 'Toalha de Banho',              'category_id' => $catCamaMesa, 'unit' => 'un', 'minimum_stock' => 40, 'is_serialized' => false, 'description' => 'Toalha de banho branca algodão 70x140cm.'],

            // ── Cozinha ─────────────────────────────────────────────
            ['name' => 'Panela de Campanha 50L',       'category_id' => $catCozinha, 'unit' => 'un', 'minimum_stock' => 5,  'is_serialized' => true,  'description' => 'Panela de alumínio industrial 50 litros para rancho.'],
            ['name' => 'Bandeja Inox Rancho',          'category_id' => $catCozinha, 'unit' => 'un', 'minimum_stock' => 80, 'is_serialized' => false, 'description' => 'Bandeja compartimentada inox para rancho.'],
            ['name' => 'Caneca de Alumínio',           'category_id' => $catCozinha, 'unit' => 'un', 'minimum_stock' => 60, 'is_serialized' => false, 'description' => 'Caneca de alumínio 300ml para café/rancho.'],
            ['name' => 'Termos 5L',                    'category_id' => $catCozinha, 'unit' => 'un', 'minimum_stock' => 8,  'is_serialized' => false, 'description' => 'Garrafa térmica industrial 5 litros.'],

            // ── Ferramentas ─────────────────────────────────────────
            ['name' => 'Pá de Campanha',               'category_id' => $catFerramentas, 'unit' => 'un', 'minimum_stock' => 15, 'is_serialized' => false, 'description' => 'Pá de campanha dobrável com cabo de madeira.'],
            ['name' => 'Picareta de Campanha',         'category_id' => $catFerramentas, 'unit' => 'un', 'minimum_stock' => 10, 'is_serialized' => false, 'description' => 'Picareta de campanha com cabo de madeira.'],
            ['name' => 'Facão de Mato',                'category_id' => $catFerramentas, 'unit' => 'un', 'minimum_stock' => 15, 'is_serialized' => false, 'description' => 'Facão de mato 18" com bainha.'],
            ['name' => 'Lanterna Tática',              'category_id' => $catFerramentas, 'unit' => 'un', 'minimum_stock' => 20, 'is_serialized' => true,  'description' => 'Lanterna tática LED 1000 lumens, corpo alumínio.'],

            // ── Material de Expediente ───────────────────────────────
            ['name' => 'Papel A4 Branco (resma)',      'category_id' => $catExpediente, 'unit' => 'rs', 'minimum_stock' => 30, 'is_serialized' => false, 'description' => 'Resma de papel A4 branco 75g, 500 folhas.'],
            ['name' => 'Caneta Esferográfica Azul',    'category_id' => $catExpediente, 'unit' => 'cx', 'minimum_stock' => 10, 'is_serialized' => false, 'description' => 'Caixa com 50 canetas esferográficas azuis.'],
            ['name' => 'Pasta Classificadora',         'category_id' => $catExpediente, 'unit' => 'un', 'minimum_stock' => 20, 'is_serialized' => false, 'description' => 'Pasta classificadora A4 com elástico.'],

            // ── Outros ──────────────────────────────────────────────
            ['name' => 'Bandeirola de Sinalização',    'category_id' => $catOutros, 'unit' => 'un', 'minimum_stock' => 10, 'is_serialized' => false, 'description' => 'Bandeirola de sinalização fluorescente.'],
            ['name' => 'Corda Estática 50m',           'category_id' => $catOutros, 'unit' => 'un', 'minimum_stock' => 5,  'is_serialized' => true,  'description' => 'Corda estática 11mm x 50m para rapel e resgate.'],
        ];

        foreach ($productsData as $p) {
            $products[$p['name']] = Product::create($p);
        }

        $this->command->info("    ✓ {$this->count($productsData)} produtos criados");

        // ═══════════════════════════════════════════════════════════
        //  3. ITENS DE ESTOQUE — com lotes, validades, localizações
        // ═══════════════════════════════════════════════════════════
        $this->command->info('  → Itens de estoque...');

        $stockCount = 0;
        $stockItemIds = [];  // para usar nos empréstimos

        // --- Japonas de Campanha (por tamanho e lote) ---
        $japonaSizes = ['PP' => 25, 'P' => 35, 'M' => 55, 'G' => 45, 'GG' => 18];
        foreach ($japonaSizes as $size => $qty) {
            $product = $products["Japona de Campanha {$size}"];
            // Lote 1 — SISCOFIS Jan/2025
            $item = StockItem::create([
                'product_id'          => $product->id,
                'batch'               => "LOTE-JAP-{$size}-2025A",
                'siscofis_entry_date' => '2025-01-15',
                'location'            => 'Galpão A — Prateleira F1',
                'subunit'             => 'Almoxarifado Central',
                'quantity'            => intval($qty * 0.6),
                'status'              => 'available',
                'notes'               => "Japona Campanha {$size} — Lote 2025A",
            ]);
            $stockItemIds["japona_{$size}_1"] = $item->id;
            StockMovement::record($item->id, 'entry', intval($qty * 0.6), $adminId, null, null, "Entrada SISCOFIS — Lote 2025A");
            $stockCount++;

            // Lote 2 — SISCOFIS Jul/2025
            $item2 = StockItem::create([
                'product_id'          => $product->id,
                'batch'               => "LOTE-JAP-{$size}-2025B",
                'siscofis_entry_date' => '2025-07-10',
                'location'            => 'Galpão A — Prateleira F2',
                'subunit'             => 'Almoxarifado Central',
                'quantity'            => intval($qty * 0.4),
                'status'              => 'available',
                'notes'               => "Japona Campanha {$size} — Lote 2025B",
            ]);
            $stockItemIds["japona_{$size}_2"] = $item2->id;
            StockMovement::record($item2->id, 'entry', intval($qty * 0.4), $adminId, null, null, "Entrada SISCOFIS — Lote 2025B");
            $stockCount++;
        }

        // --- Toldo de Campanha (serializado) ---
        for ($i = 1; $i <= 15; $i++) {
            $serial = 'TLD-2025-' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $item = StockItem::create([
                'product_id'          => $products['Toldo de Campanha']->id,
                'serial_number'       => $serial,
                'batch'               => 'LOTE-TLD-2025',
                'siscofis_entry_date' => '2025-03-20',
                'location'            => 'Galpão B — Estante T1',
                'subunit'             => 'Almoxarifado Central',
                'quantity'            => 1,
                'status'              => 'available',
                'notes'               => "Toldo de campanha 4x4m — {$serial}",
            ]);
            $stockItemIds["toldo_{$i}"] = $item->id;
            StockMovement::record($item->id, 'entry', 1, $adminId, null, null, "Entrada SISCOFIS — Toldo {$serial}");
            $stockCount++;
        }

        // --- Barracas (serializadas) ---
        foreach ([['Barraca 2 Praças', 'BRC2', 20], ['Barraca 4 Praças', 'BRC4', 12]] as [$nome, $prefixo, $qty]) {
            for ($i = 1; $i <= $qty; $i++) {
                $serial = "{$prefixo}-2024-" . str_pad($i, 3, '0', STR_PAD_LEFT);
                $item = StockItem::create([
                    'product_id'          => $products[$nome]->id,
                    'serial_number'       => $serial,
                    'batch'               => "LOTE-{$prefixo}-2024",
                    'siscofis_entry_date' => '2024-06-15',
                    'location'            => 'Galpão B — Estante B' . ($prefixo === 'BRC2' ? '1' : '2'),
                    'subunit'             => 'Almoxarifado Central',
                    'quantity'            => 1,
                    'status'              => 'available',
                ]);
                $stockItemIds["{$prefixo}_{$i}"] = $item->id;
                StockMovement::record($item->id, 'entry', 1, $adminId, null, null, "Entrada SISCOFIS — {$serial}");
                $stockCount++;
            }
        }

        // --- Demais produtos (lotes em quantidade) ---
        $bulkItems = [
            ['Gandola Camuflada M',         'LOTE-GDL-2025',  60, 'Galpão A — Prateleira F3',    '2025-02-10', null],
            ['Calça Camuflada M',           'LOTE-CLC-2025',  55, 'Galpão A — Prateleira F4',    '2025-02-10', null],
            ['Coturno de Campanha nº 40',   'LOTE-CTR-2025',  30, 'Galpão A — Prateleira C1',    '2025-04-05', null],
            ['Camiseta Camuflada M',        'LOTE-CAM-2025',  80, 'Galpão A — Prateleira F5',    '2025-03-15', null],
            ['Boné Camuflado',              'LOTE-BON-2025',  40, 'Galpão A — Prateleira F6',    '2025-03-15', null],
            ['Saco de Dormir',              'LOTE-SDR-2025',  45, 'Galpão B — Estante S1',       '2025-01-20', null],
            ['Colchonete Isolante',         'LOTE-COL-2025',  50, 'Galpão B — Estante S2',       '2025-01-20', null],
            ['Rede de Selva',               'LOTE-RDS-2025',  25, 'Galpão B — Estante S3',       '2025-05-12', null],
            ['Lona Camuflada 3x5m',         'LOTE-LON-2024',  15, 'Galpão B — Estante T2',       '2024-08-01', null],
            ['Cantil com Capa',             'LOTE-CTL-2025',  60, 'Galpão C — Armário E1',       '2025-04-10', null],
            ['Marmita Individual',          'LOTE-MRM-2025',  55, 'Galpão C — Armário E2',       '2025-04-10', null],
            ['Talher de Campanha',          'LOTE-TLH-2025',  60, 'Galpão C — Armário E3',       '2025-04-10', null],
            ['Bornal de Assalto',           'LOTE-BRN-2025',  28, 'Galpão C — Armário E4',       '2025-06-20', null],
            ['Cobertor de Lã',              'LOTE-CBT-2025',  50, 'Depósito D — Setor CM1',      '2025-03-01', null],
            ['Lençol de Solteiro',          'LOTE-LCL-2025',  70, 'Depósito D — Setor CM2',      '2025-03-01', null],
            ['Fronha Branca',               'LOTE-FRH-2025',  75, 'Depósito D — Setor CM3',      '2025-03-01', null],
            ['Toalha de Banho',             'LOTE-TWL-2025',  50, 'Depósito D — Setor CM4',      '2025-03-01', null],
            ['Bandeja Inox Rancho',         'LOTE-BDJ-2025', 100, 'Cozinha — Armário K1',        '2025-05-15', null],
            ['Caneca de Alumínio',          'LOTE-CNC-2025',  80, 'Cozinha — Armário K2',        '2025-05-15', null],
            ['Termos 5L',                   'LOTE-TRM-2025',  12, 'Cozinha — Armário K3',        '2025-05-15', null],
            ['Pá de Campanha',              'LOTE-PAC-2024',  20, 'Galpão E — Ferramentas F1',   '2024-09-01', null],
            ['Picareta de Campanha',        'LOTE-PIC-2024',  12, 'Galpão E — Ferramentas F2',   '2024-09-01', null],
            ['Facão de Mato',               'LOTE-FAC-2024',  18, 'Galpão E — Ferramentas F3',   '2024-09-01', null],
            ['Papel A4 Branco (resma)',      'LOTE-PPL-2025',  40, 'Escritório — Armário X1',     '2025-06-01', null],
            ['Caneta Esferográfica Azul',   'LOTE-CAN-2025',  15, 'Escritório — Armário X2',     '2025-06-01', null],
            ['Pasta Classificadora',        'LOTE-PST-2025',  30, 'Escritório — Armário X3',     '2025-06-01', null],
            ['Bandeirola de Sinalização',   'LOTE-BND-2025',  15, 'Galpão E — Prateleira Z1',    '2025-07-10', null],
        ];

        // Itens com validade (alimentos termos, materiais perecíveis simulados)
        $expiringItems = [
            ['Marmita Individual',       'LOTE-MRM-2024-VAL',  10, 'Galpão C — Armário E2', '2024-01-10', '2026-03-15'],
            ['Papel A4 Branco (resma)',   'LOTE-PPL-2024-VAL',   5, 'Escritório — Armário X1', '2024-06-01', '2026-02-28'],
        ];

        foreach ($bulkItems as [$nome, $lote, $qty, $loc, $siscofis, $validade]) {
            $item = StockItem::create([
                'product_id'          => $products[$nome]->id,
                'batch'               => $lote,
                'siscofis_entry_date' => $siscofis,
                'location'            => $loc,
                'subunit'             => 'Almoxarifado Central',
                'quantity'            => $qty,
                'status'              => 'available',
                'expiration_date'     => $validade,
            ]);
            $stockItemIds["bulk_{$nome}"] = $item->id;
            StockMovement::record($item->id, 'entry', $qty, $adminId, null, null, "Entrada SISCOFIS — {$lote}");
            $stockCount++;
        }

        foreach ($expiringItems as [$nome, $lote, $qty, $loc, $siscofis, $validade]) {
            $item = StockItem::create([
                'product_id'          => $products[$nome]->id,
                'batch'               => $lote,
                'siscofis_entry_date' => $siscofis,
                'location'            => $loc,
                'subunit'             => 'Almoxarifado Central',
                'quantity'            => $qty,
                'status'              => 'available',
                'expiration_date'     => $validade,
            ]);
            $stockItemIds["expiring_{$nome}"] = $item->id;
            StockMovement::record($item->id, 'entry', $qty, $adminId, null, null, "Entrada SISCOFIS — {$lote}");
            $stockCount++;
        }

        // Itens serializados restantes
        $serializedItems = [
            ['Mochila de Campanha',  'MCH', 35],
            ['Capacete M1',          'CPT', 30],
            ['Panela de Campanha 50L', 'PNL', 8],
            ['Lanterna Tática',      'LNT', 25],
            ['Corda Estática 50m',   'CRD', 8],
        ];

        foreach ($serializedItems as [$nome, $prefixo, $qty]) {
            for ($i = 1; $i <= $qty; $i++) {
                $serial = "{$prefixo}-2025-" . str_pad($i, 3, '0', STR_PAD_LEFT);
                $item = StockItem::create([
                    'product_id'          => $products[$nome]->id,
                    'serial_number'       => $serial,
                    'batch'               => "LOTE-{$prefixo}-2025",
                    'siscofis_entry_date' => '2025-05-01',
                    'location'            => 'Galpão C — Armário E5',
                    'subunit'             => 'Almoxarifado Central',
                    'quantity'            => 1,
                    'status'              => 'available',
                ]);
                $stockItemIds["{$prefixo}_{$i}"] = $item->id;
                StockMovement::record($item->id, 'entry', 1, $adminId, null, null, "Entrada SISCOFIS — {$serial}");
                $stockCount++;
            }
        }

        $this->command->info("    ✓ {$stockCount} itens de estoque criados");

        // ═══════════════════════════════════════════════════════════
        //  4. EMPRÉSTIMOS / CAUTELAS
        // ═══════════════════════════════════════════════════════════
        $this->command->info('  → Empréstimos (cautelas)...');

        $loanCount = 0;

        // --- Cautela 1: Individual — Japona M para o 2º Sgt LIMA (ativa) ---
        $loan1 = Loan::create([
            'loan_number'              => 'CAUTELA-2026-000001',
            'borrower_type'            => 'individual',
            'borrower_user_id'         => $solicitanteId,
            'loaned_by'                => $almoxarifeId,
            'loan_date'                => now()->subDays(15),
            'expected_return_date'     => now()->addDays(15),
            'status'                   => 'active',
            'notes'                    => 'Instrução de campanha — Op Fronteira',
        ]);

        // Japona M — lote 1, qty 2
        $japonaItem = StockItem::find($stockItemIds['japona_M_1']);
        LoanItem::create([
            'loan_id'       => $loan1->id,
            'stock_item_id' => $japonaItem->id,
            'quantity'      => 2,
            'condition_out' => 'bom',
        ]);
        $japonaItem->decrement('quantity', 2);
        StockMovement::record($japonaItem->id, 'loan', 2, $almoxarifeId, null, null, "Cautela {$loan1->loan_number} — Japona M");

        // Toldo 1
        $toldoItem = StockItem::find($stockItemIds['toldo_1']);
        LoanItem::create([
            'loan_id'       => $loan1->id,
            'stock_item_id' => $toldoItem->id,
            'quantity'      => 1,
            'condition_out' => 'novo',
        ]);
        $toldoItem->update(['status' => 'loaned']);
        StockMovement::record($toldoItem->id, 'loan', 1, $almoxarifeId, null, null, "Cautela {$loan1->loan_number} — Toldo TLD-2025-001");
        $loanCount++;

        // --- Cautela 2: Por seção — 1ª Cia / 1º BI (ativa) ---
        $loan2 = Loan::create([
            'loan_number'              => 'CAUTELA-2026-000002',
            'borrower_type'            => 'section',
            'borrower_section'         => '1ª Companhia',
            'borrower_organization_id' => $org1BI,
            'loaned_by'                => $almoxarifeId,
            'loan_date'                => now()->subDays(10),
            'expected_return_date'     => now()->addDays(20),
            'status'                   => 'active',
            'notes'                    => 'Material para Exercício de Bivaque',
        ]);

        // 10 Sacos de Dormir
        $sdItem = StockItem::find($stockItemIds['bulk_Saco de Dormir']);
        LoanItem::create([
            'loan_id'       => $loan2->id,
            'stock_item_id' => $sdItem->id,
            'quantity'      => 10,
            'condition_out' => 'bom',
        ]);
        $sdItem->decrement('quantity', 10);
        StockMovement::record($sdItem->id, 'loan', 10, $almoxarifeId, null, null, "Cautela {$loan2->loan_number} — Sacos de Dormir");

        // 10 Colchonetes
        $colItem = StockItem::find($stockItemIds['bulk_Colchonete Isolante']);
        LoanItem::create([
            'loan_id'       => $loan2->id,
            'stock_item_id' => $colItem->id,
            'quantity'      => 10,
            'condition_out' => 'bom',
        ]);
        $colItem->decrement('quantity', 10);
        StockMovement::record($colItem->id, 'loan', 10, $almoxarifeId, null, null, "Cautela {$loan2->loan_number} — Colchonetes");

        // 5 Barracas 2 praças
        for ($i = 1; $i <= 5; $i++) {
            $brcItem = StockItem::find($stockItemIds["BRC2_{$i}"]);
            LoanItem::create([
                'loan_id'       => $loan2->id,
                'stock_item_id' => $brcItem->id,
                'quantity'      => 1,
                'condition_out' => 'bom',
            ]);
            $brcItem->update(['status' => 'loaned']);
            StockMovement::record($brcItem->id, 'loan', 1, $almoxarifeId, null, null, "Cautela {$loan2->loan_number} — Barraca BRC2-2024-" . str_pad($i, 3, '0', STR_PAD_LEFT));
        }
        $loanCount++;

        // --- Cautela 3: Individual — 3º Sgt ROCHA (vencida — overdue) ---
        $loan3 = Loan::create([
            'loan_number'              => 'CAUTELA-2025-000015',
            'borrower_type'            => 'individual',
            'borrower_user_id'         => $solicitante2Id,
            'loaned_by'                => $almoxarife2Id,
            'loan_date'                => now()->subDays(45),
            'expected_return_date'     => now()->subDays(15),
            'status'                   => 'overdue',
            'notes'                    => 'Japona G e Boné — campo instrução anterior',
        ]);

        $japonaGItem = StockItem::find($stockItemIds['japona_G_1']);
        LoanItem::create([
            'loan_id'       => $loan3->id,
            'stock_item_id' => $japonaGItem->id,
            'quantity'      => 1,
            'condition_out' => 'bom',
        ]);
        $japonaGItem->decrement('quantity', 1);
        StockMovement::record($japonaGItem->id, 'loan', 1, $almoxarife2Id, null, null, "Cautela {$loan3->loan_number} — Japona G");

        $boneItem = StockItem::find($stockItemIds['bulk_Boné Camuflado']);
        LoanItem::create([
            'loan_id'       => $loan3->id,
            'stock_item_id' => $boneItem->id,
            'quantity'      => 1,
            'condition_out' => 'bom',
        ]);
        $boneItem->decrement('quantity', 1);
        StockMovement::record($boneItem->id, 'loan', 1, $almoxarife2Id, null, null, "Cautela {$loan3->loan_number} — Boné");
        $loanCount++;

        // --- Cautela 4: Por seção — 2ª Cia / 2º BI (devolvida) ---
        $loan4 = Loan::create([
            'loan_number'              => 'CAUTELA-2025-000010',
            'borrower_type'            => 'section',
            'borrower_section'         => '2ª Companhia',
            'borrower_organization_id' => $org2BI,
            'loaned_by'                => $almoxarifeId,
            'loan_date'                => now()->subDays(60),
            'expected_return_date'     => now()->subDays(30),
            'actual_return_date'       => now()->subDays(28),
            'received_by'             => $almoxarife2Id,
            'status'                   => 'returned',
            'notes'                    => 'Material bivaque devolvido — Manobra Divisional',
        ]);

        $canItem = StockItem::find($stockItemIds['bulk_Cantil com Capa']);
        LoanItem::create([
            'loan_id'         => $loan4->id,
            'stock_item_id'   => $canItem->id,
            'quantity'        => 15,
            'returned_quantity' => 15,
            'condition_out'   => 'bom',
            'condition_in'    => 'bom',
        ]);
        // Já devolvido, quantidade OK
        StockMovement::record($canItem->id, 'loan', 15, $almoxarifeId, null, null, "Cautela {$loan4->loan_number} — Cantis (saída)");
        StockMovement::record($canItem->id, 'return', 15, $almoxarife2Id, null, null, "Cautela {$loan4->loan_number} — Cantis (devolução)");

        $mrmItem = StockItem::find($stockItemIds['bulk_Marmita Individual']);
        LoanItem::create([
            'loan_id'         => $loan4->id,
            'stock_item_id'   => $mrmItem->id,
            'quantity'        => 15,
            'returned_quantity' => 15,
            'condition_out'   => 'bom',
            'condition_in'    => 'regular',
        ]);
        StockMovement::record($mrmItem->id, 'loan', 15, $almoxarifeId, null, null, "Cautela {$loan4->loan_number} — Marmitas (saída)");
        StockMovement::record($mrmItem->id, 'return', 15, $almoxarife2Id, null, null, "Cautela {$loan4->loan_number} — Marmitas (devolução)");
        $loanCount++;

        // --- Cautela 5: Individual — Devolução parcial ---
        $loan5 = Loan::create([
            'loan_number'              => 'CAUTELA-2026-000003',
            'borrower_type'            => 'individual',
            'borrower_user_id'         => $solicitanteId,
            'loaned_by'                => $almoxarife2Id,
            'loan_date'                => now()->subDays(7),
            'expected_return_date'     => now()->addDays(23),
            'status'                   => 'partial',
            'notes'                    => 'Mochilas + lanternas — Exercício noturno',
        ]);

        // 2 Mochilas serializadas
        for ($i = 1; $i <= 2; $i++) {
            $mchItem = StockItem::find($stockItemIds["MCH_{$i}"]);
            LoanItem::create([
                'loan_id'         => $loan5->id,
                'stock_item_id'   => $mchItem->id,
                'quantity'        => 1,
                'returned_quantity' => ($i === 1 ? 1 : 0),  // 1ª devolvida, 2ª pendente
                'condition_out'   => 'bom',
                'condition_in'    => ($i === 1 ? 'bom' : null),
            ]);
            $mchItem->update(['status' => ($i === 1 ? 'available' : 'loaned')]);
            StockMovement::record($mchItem->id, 'loan', 1, $almoxarife2Id, null, null, "Cautela {$loan5->loan_number} — Mochila MCH-2025-" . str_pad($i, 3, '0', STR_PAD_LEFT));
            if ($i === 1) {
                StockMovement::record($mchItem->id, 'return', 1, $almoxarifeId, null, null, "Cautela {$loan5->loan_number} — Mochila devolvida");
            }
        }

        // 3 Lanternas
        for ($i = 1; $i <= 3; $i++) {
            $lntItem = StockItem::find($stockItemIds["LNT_{$i}"]);
            LoanItem::create([
                'loan_id'         => $loan5->id,
                'stock_item_id'   => $lntItem->id,
                'quantity'        => 1,
                'returned_quantity' => 0,
                'condition_out'   => 'novo',
            ]);
            $lntItem->update(['status' => 'loaned']);
            StockMovement::record($lntItem->id, 'loan', 1, $almoxarife2Id, null, null, "Cautela {$loan5->loan_number} — Lanterna LNT-2025-" . str_pad($i, 3, '0', STR_PAD_LEFT));
        }
        $loanCount++;

        // --- Cautela 6: Por seção — B Log (ativa) ---
        $loan6 = Loan::create([
            'loan_number'              => 'CAUTELA-2026-000004',
            'borrower_type'            => 'section',
            'borrower_section'         => 'Seção de Transporte',
            'borrower_organization_id' => $orgBLog,
            'loaned_by'                => $almoxarifeId,
            'loan_date'                => now()->subDays(3),
            'expected_return_date'     => now()->addDays(27),
            'status'                   => 'active',
            'notes'                    => 'Material de campanha — Operação Logística',
        ]);

        // Japonas PP — 5 un
        $japPPItem = StockItem::find($stockItemIds['japona_PP_1']);
        LoanItem::create([
            'loan_id'       => $loan6->id,
            'stock_item_id' => $japPPItem->id,
            'quantity'      => 5,
            'condition_out' => 'bom',
        ]);
        $japPPItem->decrement('quantity', 5);
        StockMovement::record($japPPItem->id, 'loan', 5, $almoxarifeId, null, null, "Cautela {$loan6->loan_number} — Japona PP");

        // 3 Toldos
        for ($i = 2; $i <= 4; $i++) {
            $tItem = StockItem::find($stockItemIds["toldo_{$i}"]);
            LoanItem::create([
                'loan_id'       => $loan6->id,
                'stock_item_id' => $tItem->id,
                'quantity'      => 1,
                'condition_out' => 'bom',
            ]);
            $tItem->update(['status' => 'loaned']);
            StockMovement::record($tItem->id, 'loan', 1, $almoxarifeId, null, null, "Cautela {$loan6->loan_number} — Toldo TLD-2025-" . str_pad($i, 3, '0', STR_PAD_LEFT));
        }

        // 5 Redes de Selva
        $redeItem = StockItem::find($stockItemIds['bulk_Rede de Selva']);
        LoanItem::create([
            'loan_id'       => $loan6->id,
            'stock_item_id' => $redeItem->id,
            'quantity'      => 5,
            'condition_out' => 'bom',
        ]);
        $redeItem->decrement('quantity', 5);
        StockMovement::record($redeItem->id, 'loan', 5, $almoxarifeId, null, null, "Cautela {$loan6->loan_number} — Redes de Selva");
        $loanCount++;

        $this->command->info("    ✓ {$loanCount} cautelas criadas");

        // ═══════════════════════════════════════════════════════════
        //  5. MOVIMENTAÇÕES EXTRAS (ajustes, avarias)
        // ═══════════════════════════════════════════════════════════
        $this->command->info('  → Movimentações adicionais...');

        // Ajuste de estoque — danificou 2 bandejas
        $bdjItem = StockItem::find($stockItemIds['bulk_Bandeja Inox Rancho']);
        $bdjItem->decrement('quantity', 2);
        StockMovement::record($bdjItem->id, 'adjustment', -2, $almoxarifeId, null, null, "Ajuste: 2 bandejas danificadas em inspeção — baixa");

        // Ajuste — entrada adicional de cobertores
        $cbtItem = StockItem::find($stockItemIds['bulk_Cobertor de Lã']);
        $cbtItem->increment('quantity', 10);
        StockMovement::record($cbtItem->id, 'entry', 10, $adminId, null, null, "Reforço de estoque — nova remessa cobertores Lote 2026A");

        $this->command->info('    ✓ Ajustes registrados');

        // ═══════════════════════════════════════════════════════════
        //  RESUMO
        // ═══════════════════════════════════════════════════════════
        $totalItems = StockItem::count();
        $totalMovements = StockMovement::count();

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('  DEMONSTRAÇÃO CRIADA COM SUCESSO');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info("  Usuários:       " . User::count());
        $this->command->info("  Produtos:       " . Product::count());
        $this->command->info("  Itens Estoque:  {$totalItems}");
        $this->command->info("  Cautelas:       " . Loan::count());
        $this->command->info("  Movimentações:  {$totalMovements}");
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('Credenciais de teste (senha: helpsub2026):');
        $this->command->info('  Admin:       000000000');
        $this->command->info('  Almoxarife:  111111111 (FERREIRA)');
        $this->command->info('  Almoxarife:  222222222 (SANTOS)');
        $this->command->info('  Solicitante: 333333333 (LIMA)');
        $this->command->info('  Solicitante: 444444444 (ROCHA)');
        $this->command->info('  Auditor:     555555555 (MOREIRA)');
    }

    private function count(array $arr): int
    {
        return \count($arr);
    }
}
