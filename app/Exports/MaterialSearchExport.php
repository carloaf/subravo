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

class MaterialSearchExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected Collection $items;
    protected array $stats;
    protected string $searchTerm;

    public function __construct(Collection $items, array $stats, string $searchTerm = '')
    {
        $this->items = $items;
        $this->stats = $stats;
        $this->searchTerm = $searchTerm;
    }

    public function collection()
    {
        return $this->items;
    }

    public function headings(): array
    {
        return [
            'Material',
            'Código',
            'Ficha',
            'Tipo',
            'Dependência',
            'Unidade',
            'Quantidade',
            'Valor Unit.',
            'Valor Total',
            'Patrimônios'
        ];
    }

    public function map($item): array
    {
        return [
            $item->material_name,
            $item->material_code ?? '-',
            $item->ficha_number ?? '-',
            $item->material_type ?? '-',
            $item->upload->dependency ?? '-',
            $item->upload->unit ?? '-',
            $item->quantity,
            'R$ ' . number_format($item->unit_value, 2, ',', '.'),
            'R$ ' . number_format($item->total_value, 2, ',', '.'),
            $item->hasPatrimonyNumbers() ? $item->patrimony_count . ' patrimônio(s)' : '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669'] // emerald-600
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Resumo no final
        $lastRow = $this->items->count() + 2;
        
        $sheet->setCellValue('A' . $lastRow, 'RESUMO DA BUSCA');
        $sheet->mergeCells('A' . $lastRow . ':J' . $lastRow);
        $sheet->getStyle('A' . $lastRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F4F6']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $lastRow++;
        if ($this->searchTerm) {
            $sheet->setCellValue('A' . $lastRow, 'Termo de busca:');
            $sheet->setCellValue('B' . $lastRow, $this->searchTerm);
            $sheet->getStyle('A' . $lastRow)->getFont()->setBold(true);
            $lastRow++;
        }

        $sheet->setCellValue('A' . $lastRow, 'Total de registros:');
        $sheet->setCellValue('B' . $lastRow, number_format($this->stats['total_items']));
        $sheet->getStyle('A' . $lastRow)->getFont()->setBold(true);

        $lastRow++;
        $sheet->setCellValue('A' . $lastRow, 'Quantidade somada:');
        $sheet->setCellValue('B' . $lastRow, number_format($this->stats['total_quantity']));
        $sheet->getStyle('A' . $lastRow)->getFont()->setBold(true);

        $lastRow++;
        $sheet->setCellValue('A' . $lastRow, 'Valor total:');
        $sheet->setCellValue('B' . $lastRow, 'R$ ' . number_format($this->stats['total_value'], 2, ',', '.'));
        $sheet->getStyle('A' . $lastRow)->getFont()->setBold(true);

        return [];
    }

    public function title(): string
    {
        return 'Busca de Materiais';
    }
}
