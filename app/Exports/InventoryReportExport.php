<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class InventoryReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected Collection $items;
    protected array $stats;
    protected string $title;

    public function __construct(Collection $items, array $stats, string $title)
    {
        $this->items = $items;
        $this->stats = $stats;
        $this->title = $title;
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->items;
    }

    /**
     * Define os cabeçalhos das colunas.
     */
    public function headings(): array
    {
        return [
            'Material',
            'Tipo de Material',
            'Código Material',
            'Nr Ficha',
            'Dependência',
            'Unidade',
            'Quantidade',
            'Valor Unitário (R$)',
            'Valor Total (R$)',
            'Nº Patrimônios',
            'Conta Contábil',
            'Acervo',
            'Data Upload',
        ];
    }

    /**
     * Mapeia cada item para uma linha da planilha.
     */
    public function map($item): array
    {
        return [
            $item->material_name,
            $item->material_type ?? '—',
            $item->material_code ?? '—',
            $item->ficha_number ?? '—',
            $item->upload->dependency,
            $item->upload->unit,
            $item->quantity,
            $item->unit_value,
            $item->total_value,
            $item->hasPatrimonyNumbers() ? $item->patrimony_count : 0,
            $item->accounting_account ?? '—',
            $item->acervo ?? '—',
            $item->created_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * Estilos da planilha.
     */
    public function styles(Worksheet $sheet)
    {
        // Título do relatório na primeira linha
        $sheet->insertNewRowBefore(1, 3);
        $sheet->mergeCells('A1:M1');
        $sheet->mergeCells('A2:M2');
        
        $sheet->setCellValue('A1', $this->title);
        $sheet->setCellValue('A2', 'Gerado em ' . now()->format('d/m/Y H:i'));
        $sheet->setCellValue('A3', sprintf(
            'Total: %s itens | Quantidade: %s | Valor: R$ %s',
            number_format($this->stats['total_items']),
            number_format($this->stats['total_quantity']),
            number_format($this->stats['total_value'], 2, ',', '.')
        ));

        // Estilo do título
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '059669'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Estilo da data
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'size' => 10,
                'color' => ['rgb' => '666666'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Estilo das estatísticas
        $sheet->getStyle('A3')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F4F6'],
            ],
        ]);

        // Estilo do cabeçalho (linha 4 agora)
        $sheet->getStyle('A4:M4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Altura da linha de cabeçalho
        $sheet->getRowDimension(4)->setRowHeight(25);

        // Bordas em toda a tabela
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A4:M' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E7EB'],
                ],
            ],
        ]);

        // Alinhamento de colunas numéricas
        $sheet->getStyle('G5:I' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('J5:J' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Formato de moeda para valores
        $sheet->getStyle('H5:I' . $lastRow)->getNumberFormat()->setFormatCode('R$ #,##0.00');

        return [];
    }

    /**
     * Nome da aba da planilha.
     */
    public function title(): string
    {
        return 'Relatório de Inventário';
    }
}
