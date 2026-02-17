<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class MonthlyConsolidatedExport implements WithMultipleSheets
{
    protected Collection $itemsByDependency;
    protected array $globalStats;
    protected string $title;
    protected string $monthName;

    public function __construct(Collection $itemsByDependency, array $globalStats, string $title, string $monthName)
    {
        $this->itemsByDependency = $itemsByDependency;
        $this->globalStats = $globalStats;
        $this->title = $title;
        $this->monthName = $monthName;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Aba de resumo geral
        $sheets[] = new MonthlyConsolidatedSummarySheet($this->itemsByDependency, $this->globalStats, $this->monthName);

        // Aba para cada dependência
        foreach ($this->itemsByDependency as $dependency => $data) {
            $sheets[] = new MonthlyConsolidatedDependencySheet($dependency, $data);
        }

        return $sheets;
    }
}

class MonthlyConsolidatedSummarySheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected Collection $itemsByDependency;
    protected array $globalStats;
    protected string $monthName;

    public function __construct(Collection $itemsByDependency, array $globalStats, string $monthName)
    {
        $this->itemsByDependency = $itemsByDependency;
        $this->globalStats = $globalStats;
        $this->monthName = $monthName;
    }

    public function collection()
    {
        // Resumo por dependência
        return $this->itemsByDependency->map(function ($data, $dependency) {
            return (object)[
                'dependency' => $dependency ?? 'SEM DEPENDÊNCIA',
                'unit' => $data['upload']->unit ?? '-',
                'filename' => $data['upload']->filename,
                'date' => $data['upload']->created_at->format('d/m/Y H:i'),
                'total_items' => $data['stats']['total_items'],
                'total_quantity' => $data['stats']['total_quantity'],
                'total_value' => $data['stats']['total_value'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Dependência',
            'Unidade',
            'Arquivo PDF',
            'Data Upload',
            'Total Itens',
            'Quantidade',
            'Valor Total'
        ];
    }

    public function map($row): array
    {
        return [
            $row->dependency,
            $row->unit,
            $row->filename,
            $row->date,
            $row->total_items,
            $row->total_quantity,
            'R$ ' . number_format($row->total_value, 2, ',', '.')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4f46e5'] // indigo-600
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Resumo global
        $lastRow = $this->itemsByDependency->count() + 3;
        
        $sheet->setCellValue('A' . $lastRow, 'RESUMO GLOBAL - ' . strtoupper($this->monthName));
        $sheet->mergeCells('A' . $lastRow . ':G' . $lastRow);
        $sheet->getStyle('A' . $lastRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '3730a3'] // indigo-800
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $lastRow++;
        $sheet->setCellValue('A' . $lastRow, 'Total de Dependências:');
        $sheet->setCellValue('B' . $lastRow, $this->globalStats['total_dependencies']);
        $sheet->getStyle('A' . $lastRow)->getFont()->setBold(true);

        $lastRow++;
        $sheet->setCellValue('A' . $lastRow, 'PDFs Processados:');
        $sheet->setCellValue('B' . $lastRow, $this->globalStats['total_uploads']);
        $sheet->getStyle('A' . $lastRow)->getFont()->setBold(true);

        $lastRow++;
        $sheet->setCellValue('A' . $lastRow, 'Total de Itens:');
        $sheet->setCellValue('B' . $lastRow, number_format($this->globalStats['total_items']));
        $sheet->getStyle('A' . $lastRow)->getFont()->setBold(true);

        $lastRow++;
        $sheet->setCellValue('A' . $lastRow, 'Quantidade Total:');
        $sheet->setCellValue('B' . $lastRow, number_format($this->globalStats['total_quantity']));
        $sheet->getStyle('A' . $lastRow)->getFont()->setBold(true);

        $lastRow++;
        $sheet->setCellValue('A' . $lastRow, 'Valor Total:');
        $sheet->setCellValue('B' . $lastRow, 'R$ ' . number_format($this->globalStats['total_value'], 2, ',', '.'));
        $sheet->getStyle('A' . $lastRow)->getFont()->setBold(true);

        return [];
    }

    public function title(): string
    {
        return 'Resumo Geral';
    }
}

class MonthlyConsolidatedDependencySheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected string $dependency;
    protected array $data;

    public function __construct(string $dependency, array $data)
    {
        $this->dependency = $dependency ?? 'SEM DEPENDÊNCIA';
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data['items']->sortBy('material_name');
    }

    public function headings(): array
    {
        return [
            'Material',
            'Código',
            'Ficha',
            'Tipo',
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
            $item->quantity,
            'R$ ' . number_format($item->unit_value, 2, ',', '.'),
            'R$ ' . number_format($item->total_value, 2, ',', '.'),
            $item->hasPatrimonyNumbers() ? $item->patrimony_count . ' patrimônio(s)' : '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11
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

        // Totais
        $lastRow = $this->data['items']->count() + 3;
        
        $sheet->setCellValue('A' . $lastRow, 'TOTAIS:');
        $sheet->getStyle('A' . $lastRow)->getFont()->setBold(true);
        
        $sheet->setCellValue('E' . $lastRow, number_format($this->data['stats']['total_quantity']));
        $sheet->setCellValue('G' . $lastRow, 'R$ ' . number_format($this->data['stats']['total_value'], 2, ',', '.'));
        $sheet->getStyle('E' . $lastRow . ':G' . $lastRow)->getFont()->setBold(true);

        return [];
    }

    public function title(): string
    {
        // Excel limita nomes de abas a 31 caracteres
        $cleanName = preg_replace('/[^A-Za-z0-9 ]/', '', $this->dependency);
        return substr($cleanName, 0, 31);
    }
}
