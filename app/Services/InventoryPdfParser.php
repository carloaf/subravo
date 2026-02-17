<?php

namespace App\Services;

use Smalot\PdfParser\Parser;

class InventoryPdfParser
{
    protected string $text;
    protected array $items = [];
    protected array $header = [];

    /**
     * Parseia um PDF de inventário SISCOFIS e retorna os dados estruturados.
     */
    public function parse(string $filePath): array
    {
        $parser = new Parser();
        $pdf    = $parser->parseFile($filePath);
        $this->text = $pdf->getText();

        // Normalizar quebras de linha e espaços múltiplos
        $this->text = preg_replace('/\r\n|\r/', "\n", $this->text);

        $this->parseHeader();
        $this->parseItems();

        return [
            'header' => $this->header,
            'items'  => $this->items,
            'raw'    => $this->text,
        ];
    }

    /**
     * Extrai cabeçalho: dependência, unidade e código.
     */
    protected function parseHeader(): void
    {
        $this->header = [
            'dependency' => null,
            'unit'       => null,
            'unit_code'  => null,
        ];

        // Pegar apenas as primeiras 2000 caracteres para aumentar chances de captura
        $headerBlock = substr($this->text, 0, 2000);

        // Padrão 1: RELAÇÃO DE MATERIAL CARGA DA DEPENDÊNCIA {dep} / {unidade} - {cod}
        if (preg_match('/RELA[ÇC][ÃA]O DE MATERIAL CARGA DA DEPEND[ÊE]NCIA\s+(.+?)\s*\/\s*(.+?)\s*-\s*(\d+)\s*$/uim', $headerBlock, $m)) {
            $this->header['dependency'] = trim($m[1]);
            $unitRaw = trim($m[2]);
            $unitRaw = preg_replace('/\s+/', ' ', $unitRaw);
            $this->header['unit']      = trim($unitRaw);
            $this->header['unit_code'] = trim($m[3]);
            return;
        }

        // Padrão 2: Tentar extrair sem código (alguns PDFs podem não ter)
        if (preg_match('/RELA[ÇC][ÃA]O DE MATERIAL CARGA DA DEPEND[ÊE]NCIA\s+(.+?)\s*\/\s*(.+?)\s*$/uim', $headerBlock, $m)) {
            $this->header['dependency'] = trim($m[1]);
            $unitRaw = trim($m[2]);
            $unitRaw = preg_replace('/\s+/', ' ', $unitRaw);
            // Tentar extrair código do final da unidade se existir
            if (preg_match('/^(.+?)\s*-\s*(\d+)\s*$/', $unitRaw, $codeMatch)) {
                $this->header['unit'] = trim($codeMatch[1]);
                $this->header['unit_code'] = trim($codeMatch[2]);
            } else {
                $this->header['unit'] = $unitRaw;
            }
            return;
        }

        // Padrão 3: Buscar "Dependência:" e "Unidade:" em linhas separadas
        if (preg_match('/Depend[êe]ncia\s*:?\s*(.+?)$/uim', $headerBlock, $depMatch)) {
            $this->header['dependency'] = trim($depMatch[1]);
        }
        if (preg_match('/Unidade\s*:?\s*(.+?)$/uim', $headerBlock, $unitMatch)) {
            $this->header['unit'] = trim($unitMatch[1]);
        }
        if (preg_match('/C[óo]digo\s*:?\s*(\d+)/ui', $headerBlock, $codeMatch)) {
            $this->header['unit_code'] = trim($codeMatch[1]);
        }
    }

    /**
     * Extrai os itens de material do texto do PDF.
     *
     * Formato observado:
     *   NrFicha CodMat ContaContabil Qtd ValorUnit ValorTotal Acervo NomeMaterial
     *   Nr Patrimoniais:
     *   num1, num2, num3,
     */
    protected function parseItems(): void
    {
        $this->items = [];

        $lines = explode("\n", $this->text);
        $currentType = null;
        $currentItem = null;
        $collectingPatrimony = false;
        $patrimonyLines = '';

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Detectar seção: "1. MATERIAL PERMANENTE" ou "2. MATERIAL DE USO DURADOURO"
            if (preg_match('/^\s*(\d+)\.\s+(MATERIAL\s+.+)$/iu', $trimmed, $secMatch)) {
                // Salvar item anterior se existir
                if ($currentItem) {
                    $currentItem['patrimony_numbers'] = $this->parsePatrimonyNumbers($patrimonyLines);
                    $this->items[] = $currentItem;
                    $currentItem = null;
                    $patrimonyLines = '';
                    $collectingPatrimony = false;
                }
                $currentType = trim($secMatch[2]);
                continue;
            }

            // Ignorar linhas de cabeçalho de tabela
            if (preg_match('/Nr Ficha|Cod Mat|Conta Cont|Nome material|Rela[çc][ãa]o dos patrim/iu', $trimmed)) {
                continue;
            }

            // Ignorar linhas de header do PDF
            if (preg_match('/MINIST[ÉE]RIO|EX[ÉE]RCITO|DEP[ÓO]SITO|P[áa]gina|RELA[ÇC][ÃA]O DE MATERIAL/iu', $trimmed)) {
                continue;
            }

            // Detectar linha de totais
            if (preg_match('/Valor total do patrim[ôo]nio|N[úu]mero total|Valor total dos materiais/iu', $trimmed)) {
                // Salvar item anterior
                if ($currentItem) {
                    $currentItem['patrimony_numbers'] = $this->parsePatrimonyNumbers($patrimonyLines);
                    $this->items[] = $currentItem;
                    $currentItem = null;
                    $patrimonyLines = '';
                    $collectingPatrimony = false;
                }
                continue;
            }

            // Ignorar linhas de rodapé
            if (preg_match('/Observa[çc][ãa]o:|Detentor direto|Rela[çc][ãa]o emitida pelo SISCOFIS/iu', $trimmed)) {
                if ($currentItem) {
                    $currentItem['patrimony_numbers'] = $this->parsePatrimonyNumbers($patrimonyLines);
                    $this->items[] = $currentItem;
                    $currentItem = null;
                    $patrimonyLines = '';
                    $collectingPatrimony = false;
                }
                continue;
            }

            // Tentar detectar uma linha de item de material
            // Formato: NrFicha CodMat ContaContabil Qtd ValorUnit ValorTotal Acervo NomeMaterial
            // Ex: 458 231810 123110301  1 225,00  225,00  N  VENTILADOR DE AMBIENTE / Tipo: Parede;
            // Ficha pode ser numérica (458) ou alfanumérica (ACA1, ACA2, etc.)
            if (preg_match('/^\s*([A-Z0-9]+)\s+(\d+)\s+(\d{6,12})\s+(\d+)\s+([\d.,]+)\s+([\d.,]+)\s+([NS])\s+(.+)/u', $trimmed, $itemMatch)) {
                // Salvar item anterior
                if ($currentItem) {
                    $currentItem['patrimony_numbers'] = $this->parsePatrimonyNumbers($patrimonyLines);
                    $this->items[] = $currentItem;
                    $patrimonyLines = '';
                }

                $currentItem = [
                    'material_type'      => $currentType,
                    'material_name'      => trim($itemMatch[8]),
                    'ficha_number'       => trim($itemMatch[1]),
                    'material_code'      => trim($itemMatch[2]),
                    'accounting_account' => trim($itemMatch[3]),
                    'quantity'           => (int) $itemMatch[4],
                    'unit_value'         => $this->parseBrDecimal($itemMatch[5]),
                    'total_value'        => $this->parseBrDecimal($itemMatch[6]),
                    'acervo'             => trim($itemMatch[7]),
                    'patrimony_numbers'  => [],
                    'raw_text'           => $trimmed,
                ];
                $collectingPatrimony = false;
                continue;
            }

            // Detectar início de patrimônios
            if (preg_match('/Nr Patrimoniais\s*:/iu', $trimmed)) {
                $collectingPatrimony = true;
                // Pode ter números na mesma linha
                $afterColon = preg_replace('/.*Nr Patrimoniais\s*:\s*/iu', '', $trimmed);
                if (!empty(trim($afterColon))) {
                    $patrimonyLines .= ' ' . trim($afterColon);
                }
                continue;
            }

            // Coletar linhas de patrimônio (números separados por vírgula)
            if ($collectingPatrimony && $currentItem) {
                // Linhas de patrimônio: "104072500028788, 104072500028796, ..."
                if (preg_match('/^\s*[\d,\s]+$/u', $trimmed)) {
                    $patrimonyLines .= ' ' . $trimmed;
                    continue;
                }
                // Se a linha tem texto que não é patrimônio, pode ser continuação do nome do material
                // ou outra coisa — parar coleta
                if (!empty($trimmed) && !preg_match('/^\d/', $trimmed)) {
                    // Pode ser continuação do nome do material que ficou em outra linha
                    if ($currentItem && !preg_match('/^\d+\s+\d+\s+\d{6,}/', $trimmed)) {
                        // Adicionar ao raw_text do item
                        $currentItem['raw_text'] .= ' ' . $trimmed;
                        // Se parece com continuação do nome (texto descritivo)
                        if (!preg_match('/Nr Patrimoniais|Valor total|MATERIAL /iu', $trimmed)) {
                            $currentItem['material_name'] .= ' ' . $trimmed;
                        }
                    }
                }
            }
        }

        // Salvar último item
        if ($currentItem) {
            $currentItem['patrimony_numbers'] = $this->parsePatrimonyNumbers($patrimonyLines);
            $this->items[] = $currentItem;
        }
    }

    /**
     * Parseia string de números patrimoniais separados por vírgula.
     */
    protected function parsePatrimonyNumbers(string $rawNumbers): array
    {
        if (empty(trim($rawNumbers))) {
            return [];
        }

        $numbers = preg_split('/[,\s]+/', $rawNumbers);
        $numbers = array_filter($numbers, fn ($n) => preg_match('/^\d{5,}$/', trim($n)));
        $numbers = array_map('trim', $numbers);
        $numbers = array_values(array_unique($numbers));

        return $numbers;
    }

    /**
     * Converte valor decimal BR (1.356,16) para float.
     */
    protected function parseBrDecimal(string $value): float
    {
        $value = trim($value);
        $value = str_replace('.', '', $value);  // remove milhar
        $value = str_replace(',', '.', $value); // decimal
        return (float) $value;
    }
}
