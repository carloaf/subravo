<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\StockItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Conditional;

class StockReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    public function collection()
    {
        return Product::with(['category', 'stockItems'])
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Produto',
            'Categoria',
            'Unidade',
            'Disponível',
            'Emprestado',
            'Total',
            'Estoque Mínimo',
            'Status',
        ];
    }

    public function map($product): array
    {
        $available = $product->getAvailableStock();
        $loaned    = $product->getLoanedStock();

        return [
            $product->name,
            $product->category->name ?? '—',
            $product->unit,
            $available,
            $loaned,
            $available + $loaned,
            $product->minimum_stock,
            $product->isBelowMinimum() ? 'ABAIXO DO MÍNIMO' : 'OK',
        ];
    }

    public function title(): string
    {
        return 'Estoque Atual';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row - Emerald gradient
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669'], // Emerald-600
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // Freeze first row (header)
                $sheet->freezePane('A2');

                // Zebra striping (alternating row colors)
                for ($row = 2; $row <= $highestRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle("A{$row}:{$highestColumn}{$row}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('f0fdf4'); // Green-50
                    }
                }

                // Conditional formatting for low stock (column H - Status)
                for ($row = 2; $row <= $highestRow; $row++) {
                    $statusCell = "H{$row}";
                    $cellValue = $sheet->getCell($statusCell)->getValue();
                    
                    if ($cellValue === 'ABAIXO DO MÍNIMO') {
                        // Red background for entire row
                        $sheet->getStyle("A{$row}:{$highestColumn}{$row}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('fee2e2'); // Red-100
                        
                        // Bold red text for status
                        $sheet->getStyle($statusCell)
                            ->getFont()
                            ->setBold(true)
                            ->getColor()
                            ->setRGB('dc2626'); // Red-600
                    } else {
                        // Green badge for OK status
                        $sheet->getStyle($statusCell)
                            ->getFont()
                            ->setBold(true)
                            ->getColor()
                            ->setRGB('059669'); // Emerald-600
                    }
                }

                // Center align numeric columns (D, E, F, G)
                $sheet->getStyle("D2:G{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Add borders to all cells
                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                    ->setColor(new Color('d1d5db')); // Gray-300

                // Auto-adjust row height
                for ($row = 1; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(-1);
                }
            },
        ];
    }
}
