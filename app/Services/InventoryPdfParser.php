<?php

namespace App\Services;

use Smalot\PdfParser\Parser;

class InventoryPdfParser
{
    protected string $text;
    protected array $permanentItems = [];
    protected array $durableGoods = [];
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
            'header'          => $this->header,
            'permanent_items' => $this->permanentItems,
            'durable_goods'   => $this->durableGoods,
            'raw'             => $this->text,
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
        $this->permanentItems = [];
        $this->durableGoods = [];

        $lines = explode("\n", $this->text);
        $currentType = null;
        $currentItem = null;
        $collectingPatrimony = false;
        $patrimonyLines = '';
        
        // Estado para parsing multi-linha de USO DURADOURO
        $durableItemBuffer = null;  // Armazena item em construção
        $durableTextBuffer = '';     // Acumula texto do nome

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Detectar seção: "1. MATERIAL PERMANENTE" ou "2. MATERIAL DE USO DURADOURO"
            if (preg_match('/^\s*(\d+)\.\s+(MATERIAL\s+.+)$/iu', $trimmed, $secMatch)) {
                // Salvar item anterior se existir
                if ($currentItem) {
                    $currentItem['patrimony_numbers'] = $this->parsePatrimonyNumbers($patrimonyLines);
                    $this->addItemToCorrectArray($currentItem);
                    $currentItem = null;
                    $patrimonyLines = '';
                    $collectingPatrimony = false;
                }
                // Salvar item durável pendente se existir
                if ($durableItemBuffer) {
                    if (!empty($durableTextBuffer)) {
                        $durableItemBuffer['material_name'] = trim($durableTextBuffer);
                    }
                    $this->addItemToCorrectArray($durableItemBuffer);
                    $durableItemBuffer = null;
                    $durableTextBuffer = '';
                }
                $currentType = trim($secMatch[2]);
                continue;
            }

            // Ignorar linhas de cabeçalho de tabela
            if (preg_match('/Nr Ficha|Cod Mat|Conta Cont|Nome material|Rela[çc][ãa]o dos patrim/iu', $trimmed)) {
                continue;
            }

            // Ignorar linhas de header do PDF (devem ser linhas curtas, não linhas de dados)
            if (strlen($trimmed) < 100 && preg_match('/MINIST[ÉE]RIO|DEP[ÓO]SITO|P[áa]gina|RELA[ÇC][ÃA]O DE MATERIAL/iu', $trimmed)) {
                continue;
            }
            
            // Ignorar linha específica de cabeçalho que menciona "EXÉRCITO BRASILEIRO"
            if (preg_match('/^.*EX[ÉE]RCITO\s+BRASILEIRO\s*$/iu', $trimmed) && !preg_match('/\d{6,}/', $trimmed)) {
                continue;
            }

            // Detectar linha de totais
            if (preg_match('/Valor total do patrim[ôo]nio|N[úu]mero total|Valor total dos materiais/iu', $trimmed)) {
                // Salvar item anterior
                if ($currentItem) {
                    $currentItem['patrimony_numbers'] = $this->parsePatrimonyNumbers($patrimonyLines);
                    $this->addItemToCorrectArray($currentItem);
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
                    $this->addItemToCorrectArray($currentItem);
                    $currentItem = null;
                    $patrimonyLines = '';
                    $collectingPatrimony = false;
                }
                continue;
            }

            // Tentar detectar uma linha de item de material
            // Dois formatos possíveis:
            
            // 1. MATERIAL PERMANENTE: NrFicha CodMat ContaContabil Qtd ValorUnit ValorTotal [Acervo]NomeMaterial
            // Ex: 11 231750 123110303 1 680,00 680,00 NMESA PARA TELEFONE...
            // Nota: Acervo (N/S) pode estar colado ao valor anterior ou ao nome do material (sem espaço)
            if (preg_match('/^\s*([A-Z0-9]+)\s+(\d+)\s+(\d{6,12})\s+(\d+)\s+([\d.,]+)\s*([\d.,]+)\s*([NS])\s*(.+)/u', $trimmed, $itemMatch)) {
                // Salvar item anterior
                if ($currentItem) {
                    $currentItem['patrimony_numbers'] = $this->parsePatrimonyNumbers($patrimonyLines);
                    $this->addItemToCorrectArray($currentItem);
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
            
            // 2. MATERIAL DE USO DURADOURO - Parsing multi-linha
            // Formato pode ser:
            // Linha 1: NrFicha CodMat Conta ValorUnit NomeMaterial...
            // Linha 2: ...continuação do nome...
            // Linha 3: ValorTotal Qtd
            if ($currentType && stripos($currentType, 'USO DURADOURO') !== false) {
                // Tentar detectar INÍCIO de item: NrFicha CodMat Conta ValorUnit [texto...]
                if (preg_match('/^\s*([A-Z0-9]+)\s+(\d+)\s+(\d{6,12})\s+([\d.,]+)\s+(.+)$/u', $trimmed, $startMatch)) {
                    // Salvar item anterior se existir
                    if ($durableItemBuffer) {
                        $this->addItemToCorrectArray($durableItemBuffer);
                        $durableItemBuffer = null;
                        $durableTextBuffer = '';
                    }
                    
                    // Verificar se já tem valor total e quantidade na mesma linha
                    $remainingText = trim($startMatch[5]);
                    if (preg_match('/^(.+?)\s+([\d.]+,\d{2})\s*(\d+)\s*$/u', $remainingText, $endMatch)) {
                        // Item completo em uma linha
                        $item = [
                            'material_type'      => $currentType,
                            'material_name'      => trim($endMatch[1]),
                            'ficha_number'       => trim($startMatch[1]),
                            'material_code'      => trim($startMatch[2]),
                            'accounting_account' => trim($startMatch[3]),
                            'unit_value'         => $this->parseBrDecimal($startMatch[4]),
                            'total_value'        => $this->parseBrDecimal($endMatch[2]),
                            'quantity'           => (int) $endMatch[3],
                            'acervo'             => null,
                            'patrimony_numbers'  => [],
                            'raw_text'           => $trimmed,
                        ];
                        $this->addItemToCorrectArray($item);
                        // Não precisa de buffer aqui pois item já foi salvo
                    } else {
                        // Item multi-linha - iniciar buffer
                        $durableItemBuffer = [
                            'material_type'      => $currentType,
                            'ficha_number'       => trim($startMatch[1]),
                            'material_code'      => trim($startMatch[2]),
                            'accounting_account' => trim($startMatch[3]),
                            'unit_value'         => $this->parseBrDecimal($startMatch[4]),
                            'acervo'             => null,
                            'patrimony_numbers'  => [],
                            'raw_text'           => $trimmed,
                        ];
                        $durableTextBuffer = $remainingText;
                    }
                    continue;
                }
                
                // Se estamos com item em buffer, acumular texto ou finalizar
                if ($durableItemBuffer) {
                    // 1. Tentar linha que continua nome e termina com valores: texto + ValorTotal Qtd
                    if (preg_match('/^(.+?)\s+([\d.]+,\d{2})\s*(\d+)\s*$/u', $trimmed, $completeMatch)) {
                        // Continua o nome e captura valores finais
                        $durableTextBuffer .= ' ' . trim($completeMatch[1]);
                        $durableItemBuffer['material_name'] = trim($durableTextBuffer);
                        $durableItemBuffer['total_value'] = $this->parseBrDecimal($completeMatch[2]);
                        $durableItemBuffer['quantity'] = (int) $completeMatch[3];
                        $durableItemBuffer['raw_text'] .= ' | ' . $trimmed;
                        
                        $this->addItemToCorrectArray($durableItemBuffer);
                        $durableItemBuffer = null;
                        $durableTextBuffer = '';
                        continue;
                    }
                    // 2. Tentar linha APENAS com valores: ValorTotal Qtd (sem texto antes)
                    // Suporte a valor+qtd colados sem espaço: ex '14,951' = 14,95 + 1; '3.493,4020' = 3.493,40 + 20
                    elseif (preg_match('/^\s*([\d.]+,\d{2})\s*(\d+)\s*$/u', $trimmed, $endMatch)) {
                        // Linha final encontrada
                        $durableItemBuffer['material_name'] = trim($durableTextBuffer);
                        $durableItemBuffer['total_value'] = $this->parseBrDecimal($endMatch[1]);
                        $durableItemBuffer['quantity'] = (int) $endMatch[2];
                        $durableItemBuffer['raw_text'] .= ' | ' . $trimmed;
                        
                        $this->addItemToCorrectArray($durableItemBuffer);
                        $durableItemBuffer = null;
                        $durableTextBuffer = '';
                        continue;
                    }
                    
                    // Verificar se a linha termina com ValorTotal Qtd
                    if (preg_match('/^(.+?)\s+([\d.]+,\d{2})\s*(\d+)\s*$/u', $trimmed, $endMatch2)) {
                        // Última linha com texto + valores
                        $durableTextBuffer .= ' ' . trim($endMatch2[1]);
                        $durableItemBuffer['material_name'] = trim($durableTextBuffer);
                        $durableItemBuffer['total_value'] = $this->parseBrDecimal($endMatch2[2]);
                        $durableItemBuffer['quantity'] = (int) $endMatch2[3];
                        $durableItemBuffer['raw_text'] .= ' | ' . $trimmed;
                        
                        $this->addItemToCorrectArray($durableItemBuffer);
                        $durableItemBuffer = null;
                        $durableTextBuffer = '';
                        continue;
                    }
                    
                    // Linha de continuação do nome
                    if (!empty($trimmed) && !preg_match('/^(Página|Relação|SISCOFIS|Usuário|Data de emissão)/i', $trimmed)) {
                        $durableTextBuffer .= ' ' . $trimmed;
                        $durableItemBuffer['raw_text'] .= ' | ' . $trimmed;
                    }
                    continue;
                }
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

        // Salvar item durável pendente
        if ($durableItemBuffer) {
            // Se tem texto acumulado, usar como nome
            if (!empty($durableTextBuffer)) {
                $durableItemBuffer['material_name'] = trim($durableTextBuffer);
            }
            $this->addItemToCorrectArray($durableItemBuffer);
        }

        // Salvar último item permanente
        if ($currentItem) {
            $currentItem['patrimony_numbers'] = $this->parsePatrimonyNumbers($patrimonyLines);
            $this->addItemToCorrectArray($currentItem);
        }
    }

    /**
     * Adiciona item ao array correto baseado no material_type.
     */
    protected function addItemToCorrectArray(array $item): void
    {
        $materialType = $item['material_type'] ?? '';

        // Garantir que material_name sempre existe (evita erros ao salvar no banco)
        if (!isset($item['material_name'])) {
            $item['material_name'] = trim($item['raw_text'] ?? '');
        }
        // Safeguard: truncar nomes muito longos (acumulação excessiva do parser)
        if (mb_strlen($item['material_name']) > 400) {
            $item['material_name'] = mb_substr($item['material_name'], 0, 400);
        }
        // Material de Uso Duradouro vai para array separado
        if (stripos($materialType, 'USO DURADOURO') !== false) {
            $this->durableGoods[] = $item;
        } else {
            // Material Permanente ou qualquer outro tipo
            $this->permanentItems[] = $item;
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
