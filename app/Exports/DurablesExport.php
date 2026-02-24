<?php

namespace App\Exports;

use Illuminate\Support\Collection;
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

class DurablesExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    public function __construct(protected Collection $durableItems) {}

    public function collection(): Collection
    {
        return $this->durableItems;
    }

    public function headings(): array
    {
        return [
            'Material',
            'Cód. SISCOFIS',
            'Unidade',
            'Qtd Inventário',
            'Valor Inventário (R$)',
            'Total Estoque',
            'Disponível',
            'Cautelado',
            'Danificado',
            'Vencendo (30d)',
            'Vencidos',
            'Status Estoque',
        ];
    }

    public function map($item): array
    {
        $status = 'OK';
        if ($item->expired > 0) {
            $status = 'VENCIDOS';
        } elseif ($item->expiring_soon > 0) {
            $status = 'VENCENDO';
        } elseif ($item->product->minimum_stock > 0 && $item->available < $item->product->minimum_stock) {
            $status = 'ESTOQUE BAIXO';
        }

        return [
            $item->product->name,
            $item->product->siscofis_code ?? '—',
            $item->product->unit,
            $item->inventory_qty,
            number_format($item->inventory_value, 2, ',', '.'),
            $item->total,
            $item->available,
            $item->loaned,
            $item->damaged,
            $item->expiring_soon,
            $item->expired,
            $status,
        ];
    }

    public function title(): string
    {
        return 'Uso Duradouro';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet        = $event->sheet->getDelegate();
                $highestRow   = $sheet->getHighestRow();
                $highestCol   = $sheet->getHighestColumn();

                // Freeze header row
                $sheet->freezePane('A2');

                // Zebra striping
                for ($row = 2; $row <= $highestRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:{$highestCol}{$row}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('f0fdf4'); // Green-50
                    }
                }

                // Conditional formatting on Status column (L)
                for ($row = 2; $row <= $highestRow; $row++) {
                    $val = $sheet->getCell("L{$row}")->getValue();
                    if ($val === 'VENCIDOS') {
                        $sheet->getStyle("A{$row}:{$highestCol}{$row}")
                            ->getFill()->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('fee2e2');
                        $sheet->getStyle("L{$row}")->getFont()->setBold(true)->getColor()->setRGB('dc2626');
                    } elseif ($val === 'VENCENDO') {
                        $sheet->getStyle("A{$row}:{$highestCol}{$row}")
                            ->getFill()->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('fef9c3');
                        $sheet->getStyle("L{$row}")->getFont()->setBold(true)->getColor()->setRGB('d97706');
                    } elseif ($val === 'ESTOQUE BAIXO') {
                        $sheet->getStyle("A{$row}:{$highestCol}{$row}")
                            ->getFill()->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('ffedd5');
                        $sheet->getStyle("L{$row}")->getFont()->setBold(true)->getColor()->setRGB('ea580c');
                    } else {
                        $sheet->getStyle("L{$row}")->getFont()->setBold(true)->getColor()->setRGB('059669');
                    }
                }

                // Center align numeric columns (D, F, G, H, I, J, K)
                $sheet->getStyle("D2:D{$highestRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F2:K{$highestRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E2:E{$highestRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                // Borders
                $sheet->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                    ->setColor(new Color('d1d5db'));

                // Auto row height
                for ($row = 1; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(-1);
                }
            },
        ];
    }
}
