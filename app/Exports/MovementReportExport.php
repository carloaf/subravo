<?php

namespace App\Exports;

use App\Models\StockMovement;
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

class MovementReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    public function __construct(
        protected ?string $dateFrom = null,
        protected ?string $dateTo = null,
    ) {}

    public function collection()
    {
        $query = StockMovement::with(['stockItem.product', 'performedBy']);

        if ($this->dateFrom && $this->dateTo) {
            $query->forDateRange($this->dateFrom, $this->dateTo);
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'Data/Hora',
            'Produto',
            'Tipo',
            'Quantidade',
            'Responsável',
            'Referência',
            'Observações',
        ];
    }

    public function map($movement): array
    {
        $typeLabels = StockMovement::TYPES;

        return [
            $movement->created_at->format('d/m/Y H:i'),
            $movement->stockItem?->product?->name ?? '—',
            $typeLabels[$movement->movement_type] ?? $movement->movement_type,
            ($movement->quantity >= 0 ? '+' : '') . $movement->quantity,
            $movement->performedBy?->war_name ?? '—',
            $movement->reference_type ? ucfirst($movement->reference_type) . " #{$movement->reference_id}" : '—',
            $movement->notes ?? '',
        ];
    }

    public function title(): string
    {
        return 'Movimentações';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669'],
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

                $sheet->freezePane('A2');

                // Zebra striping
                for ($row = 2; $row <= $highestRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle("A{$row}:{$highestColumn}{$row}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('f0fdf4');
                    }
                }

                // Color-code quantity column (D) based on positive/negative
                for ($row = 2; $row <= $highestRow; $row++) {
                    $qtyCell = "D{$row}";
                    $value = $sheet->getCell($qtyCell)->getValue();
                    
                    if (is_string($value) && str_starts_with($value, '+')) {
                        $sheet->getStyle($qtyCell)
                            ->getFont()
                            ->setBold(true)
                            ->getColor()
                            ->setRGB('059669'); // Green for positive
                    } elseif (is_string($value) && str_starts_with($value, '-')) {
                        $sheet->getStyle($qtyCell)
                            ->getFont()
                            ->setBold(true)
                            ->getColor()
                            ->setRGB('dc2626'); // Red for negative
                    }
                }

                // Center align
                $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C2:D{$highestRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Borders
                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                    ->setColor(new Color('d1d5db'));

                for ($row = 1; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(-1);
                }
            },
        ];
    }
}
