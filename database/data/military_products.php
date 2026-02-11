<?php

/**
 * Lista mestre de produtos militares de intendência
 * 
 * Estrutura:
 * - name: Nome do produto
 * - category: Nome da categoria (deve existir na tabela categories)
 * - unit: Unidade de medida (un, pç, par, kg, rs, cx, cj, etc.)
 * - siscofis_code: Código/Ficha SISCOFIS do material
 * - shelf_life_months: Validade em meses após entrada SISCOFIS (null = não perece)
 * - is_serialized: Controle por número de série (true/false)
 * - is_durable: Material de uso duradouro (true/false)
 * - minimum_stock: Quantidade mínima de estoque para alerta
 * - description: Descrição detalhada do material
 */

return [
    // ═══════════════════════════════════════════════════════════
    //  FARDAMENTO
    // ═══════════════════════════════════════════════════════════
    [
        'name' => 'Japona de Campanha PP',
        'category' => 'Fardamento',
        'unit' => 'pç',
        'siscofis_code' => 'FRD-JAP-PP',
        'shelf_life_months' => 36,
        'is_serialized' => false,
        'is_durable' => false,
        'is_durable' => false,
        'minimum_stock' => 20,
        'description' => 'Japona de campanha camuflada, tamanho PP.',
    ],
    [
        'name' => 'Japona de Campanha P',
        'category' => 'Fardamento',
        'unit' => 'pç',
        'siscofis_code' => 'FRD-JAP-P',
        'shelf_life_months' => 36,
        'is_serialized' => false,
        'is_durable' => false,
        'minimum_stock' => 30,
        'description' => 'Japona de campanha camuflada, tamanho P.',
    ],
    [
        'name' => 'Japona de Campanha M',
        'category' => 'Fardamento',
        'unit' => 'pç',
        'siscofis_code' => 'FRD-JAP-M',
        'shelf_life_months' => 36,
        'is_serialized' => false,
        'is_durable' => false,
        'minimum_stock' => 50,
        'description' => 'Japona de campanha camuflada, tamanho M.',
    ],
    [
        'name' => 'Japona de Campanha G',
        'category' => 'Fardamento',
        'unit' => 'pç',
        'siscofis_code' => 'FRD-JAP-G',
        'shelf_life_months' => 36,
        'is_serialized' => false,
        'is_durable' => false,
        'minimum_stock' => 40,
        'description' => 'Japona de campanha camuflada, tamanho G.',
    ],
    [
        'name' => 'Japona de Campanha GG',
        'category' => 'Fardamento',
        'unit' => 'pç',
        'siscofis_code' => 'FRD-JAP-GG',
        'shelf_life_months' => 36,
        'is_serialized' => false,
        'is_durable' => false,
        'minimum_stock' => 15,
        'description' => 'Japona de campanha camuflada, tamanho GG.',
    ],
    [
        'name' => 'Gandola Camuflada M',
        'category' => 'Fardamento',
        'unit' => 'pç',
        'siscofis_code' => 'FRD-GDL-M',
        'shelf_life_months' => 24,
        'is_serialized' => false,
        'is_durable' => false,
        'minimum_stock' => 40,
        'description' => 'Gandola camuflada padrão EB, tamanho M.',
    ],
    [
        'name' => 'Calça Camuflada M',
        'category' => 'Fardamento',
        'unit' => 'pç',
        'siscofis_code' => 'FRD-CLC-M',
        'shelf_life_months' => 24,
        'is_serialized' => false,
        'is_durable' => false,
        'minimum_stock' => 40,
        'description' => 'Calça camuflada padrão EB, tamanho M.',
    ],
    [
        'name' => 'Coturno de Campanha nº 40',
        'category' => 'Fardamento',
        'unit' => 'par',
        'siscofis_code' => 'FRD-CTR-40',
        'shelf_life_months' => 60,
        'is_serialized' => false,
        'is_durable' => false,
        'minimum_stock' => 25,
        'description' => 'Coturno de campanha preto, numeração 40.',
    ],
    [
        'name' => 'Camiseta Camuflada M',
        'category' => 'Fardamento',
        'unit' => 'pç',
        'siscofis_code' => 'FRD-CAM-M',
        'shelf_life_months' => 18,
        'is_serialized' => false,
        'is_durable' => false,
        'minimum_stock' => 60,
        'description' => 'Camiseta meia-manga camuflada, tamanho M.',
    ],
    [
        'name' => 'Boné Camuflado',
        'category' => 'Fardamento',
        'unit' => 'un',
        'siscofis_code' => 'FRD-BON-001',
        'shelf_life_months' => 24,
        'is_serialized' => false,
        'is_durable' => false,
        'minimum_stock' => 30,
        'description' => 'Boné camuflado padrão EB.',
    ],

    // ═══════════════════════════════════════════════════════════
    //  MATERIAL DE CAMPING
    // ═══════════════════════════════════════════════════════════
    [
        'name' => 'Toldo de Campanha',
        'category' => 'Material de Camping',
        'unit' => 'un',
        'siscofis_code' => 'CMP-TLD-4X4',
        'shelf_life_months' => 60,
        'is_serialized' => true,
        'is_durable' => false,
        'minimum_stock' => 10,
        'description' => 'Toldo de campanha impermeável 4x4m, cor verde oliva. Material resistente para bivaque.',
    ],
    [
        'name' => 'Barraca 2 Praças',
        'category' => 'Material de Camping',
        'unit' => 'un',
        'siscofis_code' => 'CMP-BRC-2P',
        'shelf_life_months' => 60,
        'is_serialized' => true,
        'is_durable' => false,
        'minimum_stock' => 15,
        'description' => 'Barraca para 2 praças, cor camuflada, com sobreteto.',
    ],
    [
        'name' => 'Barraca 4 Praças',
        'category' => 'Material de Camping',
        'unit' => 'un',
        'siscofis_code' => 'CMP-BRC-4P',
        'shelf_life_months' => 60,
        'is_serialized' => true,
        'is_durable' => false,
        'minimum_stock' => 8,
        'description' => 'Barraca para 4 praças, cor camuflada, com sobreteto.',
    ],
    [
        'name' => 'Saco de Dormir',
        'category' => 'Material de Camping',
        'unit' => 'un',
        'siscofis_code' => 'CMP-SDR-001',
        'shelf_life_months' => 48,
        'is_serialized' => false,
        'is_durable' => false,
        'minimum_stock' => 30,
        'description' => 'Saco de dormir térmico, -5°C a +10°C.',
    ],
    [
        'name' => 'Colchonete Isolante',
        'category' => 'Material de Camping',
        'unit' => 'un',
        'siscofis_code' => 'CMP-COL-001',
        'shelf_life_months' => 36,
        'is_serialized' => false,
        'is_durable' => false,
        'minimum_stock' => 40,
        'description' => 'Colchonete isolante térmico EVA, 180x55cm.',
    ],
    [
        'name' => 'Rede de Selva',
        'category' => 'Material de Camping',
        'unit' => 'un',
        'siscofis_code' => 'CMP-RDS-001',
        'shelf_life_months' => 48,
        'is_serialized' => false,
        'is_durable' => false,
        'minimum_stock' => 20,
        'description' => 'Rede de selva com mosquiteiro e sobreteto.',
    ],
    [
        'name' => 'Lona Camuflada 3x5m',
        'category' => 'Material de Camping',
        'unit' => 'un',
        'siscofis_code' => 'CMP-LON-3X5',
        'shelf_life_months' => 48,
        'is_serialized' => false,
        'is_durable' => false,
        'minimum_stock' => 10,
        'description' => 'Lona camuflada impermeável 3x5m para bivaque.',
    ],

    // ═══════════════════════════════════════════════════════════
    //  EQUIPAMENTO INDIVIDUAL
    // ═══════════════════════════════════════════════════════════
    [
        'name' => 'Mochila de Campanha',
        'category' => 'Equipamento Individual',
        'unit' => 'un',
        'siscofis_code' => 'EQP-MCH-70L',
        'shelf_life_months' => 60,
        'is_serialized' => true,
        'is_durable' => false,
        'minimum_stock' => 30,
        'description' => 'Mochila de campanha 70L, camuflada.',
    ],
    [
        'name' => 'Cantil com Capa',
        'category' => 'Equipamento Individual',
        'unit' => 'un',
        'siscofis_code' => 'EQP-CTL-900',
        'shelf_life_months' => 120,
        'is_serialized' => false,
        'is_durable' => true,
        'minimum_stock' => 50,
        'description' => 'Cantil alumínio 900ml com capa camuflada.',
    ],
    [
        'name' => 'Marmita Individual',
        'category' => 'Equipamento Individual',
        'unit' => 'un',
        'siscofis_code' => 'EQP-MRM-001',
        'shelf_life_months' => 120,
        'is_serialized' => false,
        'is_durable' => true,
        'minimum_stock' => 50,
        'description' => 'Marmita de alumínio individual com tampa.',
    ],
    [
        'name' => 'Talher de Campanha',
        'category' => 'Equipamento Individual',
        'unit' => 'cj',
        'siscofis_code' => 'EQP-TLH-001',
        'shelf_life_months' => 120,
        'is_serialized' => false,
        'is_durable' => true,
        'minimum_stock' => 50,
        'description' => 'Conjunto faca/garfo/colher inox dobrável.',
    ],
    [
        'name' => 'Bornal de Assalto',
        'category' => 'Equipamento Individual',
        'unit' => 'un',
        'siscofis_code' => 'EQP-BRN-001',
        'shelf_life_months' => 36,
        'is_serialized' => false,
        'is_durable' => false,
        'minimum_stock' => 20,
        'description' => 'Bornal de assalto camuflado, fixação no cinturão.',
    ],
    [
        'name' => 'Capacete M1',
        'category' => 'Equipamento Individual',
        'unit' => 'un',
        'siscofis_code' => 'EQP-CPT-M1',
        'shelf_life_months' => null, // Não perece
        'is_serialized' => true,
        'is_durable' => true,
        'minimum_stock' => 25,
        'description' => 'Capacete de combate modelo M1 com jugular.',
    ],

    // ═══════════════════════════════════════════════════════════
    //  CAMA E MESA
    // ═══════════════════════════════════════════════════════════
    [
        'name' => 'Cobertor de Lã',
        'category' => 'Cama e Mesa',
        'unit' => 'un',
        'siscofis_code' => 'CAM-CBT-001',
        'shelf_life_months' => 60,
        'is_serialized' => false,
        'is_durable' => false,
        'minimum_stock' => 40,
        'description' => 'Cobertor de lã verde oliva, solteiro.',
    ],
    [
        'name' => 'Lençol de Solteiro',
        'category' => 'Cama e Mesa',
        'unit' => 'un',
        'siscofis_code' => 'CAM-LCL-001',
        'shelf_life_months' => 24,
        'is_serialized' => false,
        'is_durable' => false,
        'minimum_stock' => 60,
        'description' => 'Lençol branco de algodão, solteiro.',
    ],
    [
        'name' => 'Fronha Branca',
        'category' => 'Cama e Mesa',
        'unit' => 'un',
        'siscofis_code' => 'CAM-FRH-001',
        'shelf_life_months' => 24,
        'is_serialized' => false,
        'is_durable' => false,
        'minimum_stock' => 60,
        'description' => 'Fronha branca de algodão padrão.',
    ],
    [
        'name' => 'Toalha de Banho',
        'category' => 'Cama e Mesa',
        'unit' => 'un',
        'siscofis_code' => 'CAM-TWL-001',
        'shelf_life_months' => 36,
        'is_serialized' => false,
        'is_durable' => false,
        'minimum_stock' => 40,
        'description' => 'Toalha de banho branca algodão 70x140cm.',
    ],

    // ═══════════════════════════════════════════════════════════
    //  COZINHA
    // ═══════════════════════════════════════════════════════════
    [
        'name' => 'Panela de Campanha 50L',
        'category' => 'Cozinha',
        'unit' => 'un',
        'siscofis_code' => 'COZ-PNL-50L',
        'shelf_life_months' => null, // Não perece
        'is_serialized' => true,
        'is_durable' => true,
        'minimum_stock' => 5,
        'description' => 'Panela de alumínio industrial 50 litros para rancho.',
    ],
    [
        'name' => 'Bandeja Inox Rancho',
        'category' => 'Cozinha',
        'unit' => 'un',
        'siscofis_code' => 'COZ-BDJ-001',
        'shelf_life_months' => null, // Não perece
        'is_serialized' => false,
        'is_durable' => true,
        'minimum_stock' => 80,
        'description' => 'Bandeja compartimentada inox para rancho.',
    ],
    [
        'name' => 'Caneca de Alumínio',
        'category' => 'Cozinha',
        'unit' => 'un',
        'siscofis_code' => 'COZ-CNC-300',
        'shelf_life_months' => null, // Não perece
        'is_serialized' => false,
        'is_durable' => true,
        'minimum_stock' => 60,
        'description' => 'Caneca de alumínio 300ml para café/rancho.',
    ],
    [
        'name' => 'Termos 5L',
        'category' => 'Cozinha',
        'unit' => 'un',
        'siscofis_code' => 'COZ-TRM-5L',
        'shelf_life_months' => 120,
        'is_serialized' => false,
        'is_durable' => false,
        'minimum_stock' => 8,
        'description' => 'Garrafa térmica industrial 5 litros.',
    ],

    // ═══════════════════════════════════════════════════════════
    //  FERRAMENTAS
    // ═══════════════════════════════════════════════════════════
    [
        'name' => 'Pá de Campanha',
        'category' => 'Ferramentas',
        'unit' => 'un',
        'siscofis_code' => 'FER-PAC-001',
        'shelf_life_months' => null, // Não perece
        'is_serialized' => false,
        'is_durable' => true,
        'minimum_stock' => 15,
        'description' => 'Pá de campanha dobrável com cabo de madeira.',
    ],
    [
        'name' => 'Picareta de Campanha',
        'category' => 'Ferramentas',
        'unit' => 'un',
        'siscofis_code' => 'FER-PIC-001',
        'shelf_life_months' => null, // Não perece
        'is_serialized' => false,
        'is_durable' => true,
        'minimum_stock' => 10,
        'description' => 'Picareta de campanha com cabo de madeira.',
    ],
    [
        'name' => 'Facão de Mato',
        'category' => 'Ferramentas',
        'unit' => 'un',
        'siscofis_code' => 'FER-FAC-18',
        'shelf_life_months' => null, // Não perece
        'is_serialized' => false,
        'is_durable' => true,
        'minimum_stock' => 15,
        'description' => 'Facão de mato 18" com bainha.',
    ],
    [
        'name' => 'Lanterna Tática',
        'category' => 'Ferramentas',
        'unit' => 'un',
        'siscofis_code' => 'FER-LNT-1000',
        'shelf_life_months' => 60,
        'is_serialized' => true,
        'is_durable' => false,
        'minimum_stock' => 20,
        'description' => 'Lanterna tática LED 1000 lumens, corpo alumínio.',
    ],

    // ═══════════════════════════════════════════════════════════
    //  MATERIAL DE EXPEDIENTE
    // ═══════════════════════════════════════════════════════════
    [
        'name' => 'Papel A4 Branco (resma)',
        'category' => 'Material de Expediente',
        'unit' => 'rs',
        'siscofis_code' => 'EXP-PPL-A4',
        'shelf_life_months' => 24,
        'is_serialized' => false,
        'is_durable' => false,
        'minimum_stock' => 30,
        'description' => 'Resma de papel A4 branco 75g, 500 folhas.',
    ],
    [
        'name' => 'Caneta Esferográfica Azul',
        'category' => 'Material de Expediente',
        'unit' => 'cx',
        'siscofis_code' => 'EXP-CAN-AZ',
        'shelf_life_months' => 36,
        'is_serialized' => false,
        'is_durable' => false,
        'minimum_stock' => 10,
        'description' => 'Caixa com 50 canetas esferográficas azuis.',
    ],
    [
        'name' => 'Pasta Classificadora',
        'category' => 'Material de Expediente',
        'unit' => 'un',
        'siscofis_code' => 'EXP-PST-A4',
        'shelf_life_months' => null, // Não perece
        'is_serialized' => false,
        'is_durable' => true,
        'minimum_stock' => 20,
        'description' => 'Pasta classificadora A4 com elástico.',
    ],

    // ═══════════════════════════════════════════════════════════
    //  OUTROS
    // ═══════════════════════════════════════════════════════════
    [
        'name' => 'Bandeirola de Sinalização',
        'category' => 'Outros',
        'unit' => 'un',
        'siscofis_code' => 'OUT-BND-001',
        'shelf_life_months' => 24,
        'is_serialized' => false,
        'is_durable' => false,
        'minimum_stock' => 10,
        'description' => 'Bandeirola de sinalização fluorescente.',
    ],
    [
        'name' => 'Corda Estática 50m',
        'category' => 'Outros',
        'unit' => 'un',
        'siscofis_code' => 'OUT-CRD-50M',
        'shelf_life_months' => 60,
        'is_serialized' => true,
        'is_durable' => true,
        'minimum_stock' => 5,
        'description' => 'Corda estática 11mm x 50m para rapel e resgate.',
    ],
];
