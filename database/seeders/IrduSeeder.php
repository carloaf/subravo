<?php

namespace Database\Seeders;

use App\Models\IrduItem;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IrduSeeder extends Seeder
{
    /**
     * Importa dados do IRDU.json e atualiza shelf_life_months dos produtos existentes.
     */
    public function run(): void
    {
        $this->command->info('📋 Importando dados IRDU...');

        $jsonPath = base_path('IRDU.json');

        if (! file_exists($jsonPath)) {
            $this->command->error('Arquivo IRDU.json não encontrado na raiz do projeto.');
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);

        if (! $data || ! isset($data['anexos'])) {
            $this->command->error('Formato inválido do IRDU.json.');
            return;
        }

        $count = 0;

        foreach ($data['anexos'] as $annexKey => $annex) {
            $annexTitle = $annex['titulo'] ?? '';
            $items = $annex['itens'] ?? [];

            foreach ($items as $item) {
                // Determina a duração representativa (máxima entre todas as dotações)
                $maxMonths = null;
                $representativeDurationText = 'Indeterminado';

                foreach ($item['dotacoes'] as $dotacao) {
                    $months = IrduItem::parseDurationToMonths($dotacao['duracao']);
                    if ($months !== null && ($maxMonths === null || $months > $maxMonths)) {
                        $maxMonths = $months;
                        $representativeDurationText = $dotacao['duracao'];
                    }
                }

                // Se todas são indeterminado, mantém null
                if ($maxMonths === null) {
                    $allIndeterminate = true;
                    foreach ($item['dotacoes'] as $dotacao) {
                        if (mb_strtolower(trim($dotacao['duracao'])) !== 'indeterminado') {
                            $allIndeterminate = false;
                            break;
                        }
                    }
                    if ($allIndeterminate) {
                        $representativeDurationText = 'Indeterminado';
                    }
                }

                IrduItem::updateOrCreate(
                    [
                        'annex' => $annexKey,
                        'item_number' => $item['numero'],
                    ],
                    [
                        'annex_title' => $annexTitle,
                        'material_name' => $item['material'],
                        'duration_text' => $representativeDurationText,
                        'duration_months' => $maxMonths,
                        'dotacoes' => $item['dotacoes'],
                    ]
                );

                $count++;
            }
        }

        $this->command->info("  → {$count} itens IRDU importados.");

        // ═══════════════════════════════════════════════════════════
        //  Atualiza shelf_life_months dos produtos existentes
        // ═══════════════════════════════════════════════════════════
        $this->updateProductShelfLife();
    }

    /**
     * Atualiza shelf_life_months dos produtos cadastrados com base nos itens IRDU.
     *
     * Usa keyword-scoring: extrai nome base + cor/tipo do produto e pontua
     * cada item IRDU pela sobreposição de palavras (stem de 4 caracteres).
     */
    private function updateProductShelfLife(): void
    {
        $this->command->info('  → Atualizando validade dos produtos com base no IRDU...');

        $irduItems = IrduItem::whereNotNull('duration_months')->get();
        $products = Product::withoutGlobalScopes()->get();
        $updated = 0;
        $notMatched = [];

        foreach ($products as $product) {
            $match = $this->findBestIrduMatch($product->name, $irduItems);

            if ($match) {
                $product->shelf_life_months = $match->duration_months;
                $product->save();
                $updated++;
                $this->command->line("    ✓ {$product->name} → {$match->duration_months} meses ({$match->material_name})");
            } else {
                $base = trim(explode('/', $product->name)[0]);
                $notMatched[$base] = true;
            }
        }

        $this->command->info("  → {$updated} produtos atualizados com validade IRDU.");

        if ($notMatched) {
            $this->command->warn('  → Produtos sem correspondência IRDU:');
            foreach (array_keys($notMatched) as $name) {
                $this->command->line("    ✗ {$name}");
            }
        }
    }

    /**
     * Encontra o melhor match IRDU para um nome de produto.
     *
     * Estratégia:
     * 1. Anchor: a primeira palavra do base name do produto deve corresponder
     *    à primeira palavra do item IRDU (evita matches falsos por cor/tipo).
     * 2. Score: sobreposição total de palavras-chave (base + atributos).
     * 3. Tiebreak: IRDU match ratio (score / total IRDU words) — prefere
     *    itens IRDU mais específicos (maior % de palavras correspondidas).
     * 4. Threshold: score >= 2 para aceitar.
     */
    private function findBestIrduMatch(string $productName, $irduItems): ?IrduItem
    {
        $parsed = $this->parseProductName($productName);
        $baseWords = $parsed['baseWords'];
        $attrWords = $parsed['attrWords'];
        $allWords = array_values(array_unique(array_merge($baseWords, $attrWords)));

        if (empty($baseWords)) {
            return null;
        }

        $firstBaseWord = $baseWords[0];
        $bestMatch = null;
        $bestScore = 0;
        $bestRatio = 0.0;

        foreach ($irduItems as $irdu) {
            $irduWords = $this->tokenize($irdu->material_name);

            if (empty($irduWords)) {
                continue;
            }

            // Anchor: primeira palavra do produto deve corresponder à primeira do IRDU
            if (! $this->stemMatch($firstBaseWord, $irduWords[0])) {
                continue;
            }

            $score = $this->calculateOverlap($allWords, $irduWords);
            $ratio = $score / count($irduWords);

            // Prefere maior score; em caso de empate, maior ratio (IRDU mais específico)
            if ($score > $bestScore || ($score === $bestScore && $ratio > $bestRatio)) {
                $bestMatch = $irdu;
                $bestScore = $score;
                $bestRatio = $ratio;
            }
        }

        return $bestScore >= 2 ? $bestMatch : null;
    }

    /**
     * Extrai palavras-chave separadas em base (antes de "/") e atributos (cor + tipo).
     *
     * Formato esperado: "BASE_NAME / Cor: X; Tipo: Y; Tamanho: Z; ..."
     */
    private function parseProductName(string $productName): array
    {
        $parts = explode('/', $productName, 2);
        $base = trim($parts[0]);
        $details = isset($parts[1]) ? trim($parts[1]) : '';

        // Aplicar aliases conhecidos no base name
        $base = str_ireplace(
            ['NYLON', 'INSÍGNA'],
            ['NÁILON', 'INSÍGNIA'],
            $base
        );

        // Extrair cor
        $color = '';
        if (preg_match('/Cor:\s*([^;]+)/i', $details, $m)) {
            $color = trim($m[1]);
        }

        // Extrair tipo
        $type = '';
        if (preg_match('/Tipo:\s*([^;]+)/i', $details, $m)) {
            $type = trim($m[1]);
        }

        $baseWords = $this->tokenize($base);
        $attrWords = $this->tokenize($color . ' ' . $type);

        // Remover da lista de atributos palavras já presentes na base
        $attrWords = array_values(array_diff($attrWords, $baseWords));

        return [
            'baseWords' => $baseWords,
            'attrWords' => $attrWords,
        ];
    }

    /**
     * Tokeniza string em palavras significativas (>= 3 caracteres).
     */
    private function tokenize(string $str): array
    {
        $str = mb_strtolower(trim($str));
        // Remove pontuação e separadores
        $str = str_replace(['-', '(', ')', ',', ';', ':', '"', '"', '"'], ' ', $str);
        $str = preg_replace('/\s+/', ' ', $str);

        $words = explode(' ', $str);

        // Filtrar palavras muito curtas e stop words
        $stopWords = ['de', 'do', 'da', 'dos', 'das', 'e', 'em', 'ou', 'para', 'por', 'com', 'sem', 'um', 'uma', 'no', 'na'];
        $filtered = [];

        foreach ($words as $w) {
            $w = trim($w);
            if (mb_strlen($w) >= 3 && ! in_array($w, $stopWords, true)) {
                $filtered[] = $w;
            }
        }

        return array_values(array_unique($filtered));
    }

    /**
     * Calcula sobreposição de palavras entre produto e IRDU (stem 4 chars).
     */
    private function calculateOverlap(array $productWords, array $irduWords): int
    {
        $score = 0;

        foreach ($productWords as $pw) {
            foreach ($irduWords as $iw) {
                if ($this->stemMatch($pw, $iw)) {
                    $score++;
                    break; // Conta cada palavra do produto apenas uma vez
                }
            }
        }

        return $score;
    }

    /**
     * Compara duas palavras por stem de 4 caracteres (tolera gênero: preto/preta).
     */
    private function stemMatch(string $a, string $b): bool
    {
        if ($a === $b) {
            return true;
        }

        $minLen = min(mb_strlen($a), mb_strlen($b));

        if ($minLen < 3) {
            return false;
        }

        $stemLen = min($minLen, 4);

        return mb_substr($a, 0, $stemLen) === mb_substr($b, 0, $stemLen);
    }
}
