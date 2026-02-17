<?php

namespace App\Exports;

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

class ExpirationAlertExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    public function __construct(
        protected int $days = 60,
    ) {}

    public function collection()
    {
        return StockItem::with('product.category')
            ->expiringSoon($this->days)
            ->orderBy('expiration_date')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Produto',
            'Categoria',
            'Lote',
            'Nº Série',
            'Quantidade',
            'Data Validade',
            'Dias Restantes',
            'Localização',
            'Status',
        ];
    }

    public function map($item): array
    {
        $daysLeft  = (int) now()->diffInDays($item->expiration_date, false);
        $isExpired = $daysLeft < 0;

        return [
            $item->product?->name ?? '—',
            $item->product?->category?->name ?? '—',
            $item->batch ?? '—',
            $item->serial_number ?? '—',
            $item->quantity,
            $item->expiration_date->format('d/m/Y'),
            $isExpired ? "VENCIDO ({$daysLeft}d)" : "{$daysLeft} dias",
            $item->location ?? '—',
            $isExpired ? 'VENCIDO' : ($daysLeft <= 15 ? 'CRÍTICO' : 'ATENÇÃO'),
        ];
    }

    public function title(): string
    {
        return 'Itens Próximos da Validade';
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
                $items = $this->collection();

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

                // Conditional formatting based on status (column I)
                $rowIndex = 2;
                foreach ($items as $item) {
                    $daysLeft = (int) now()->diffInDays($item->expiration_date, false);
                    $statusCell = "I{$rowIndex}";
                    
                    if ($daysLeft < 0) {
                        // Red for expired
                        $sheet->getStyle("A{$rowIndex}:{$highestColumn}{$rowIndex}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('fee2e2'); // Red-100
                        
                        $sheet->getStyle($statusCell)
                            ->getFont()
                            ->setBold(true)
                            ->getColor()
                            ->setRGB('dc2626'); // Red-600
                    } elseif ($daysLeft <= 15) {
                        // Amber for critical
                        $sheet->getStyle("A{$rowIndex}:{$highestColumn}{$rowIndex}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('fef3c7'); // Amber-100
                        
                        $sheet->getStyle($statusCell)
                            ->getFont()
                            ->setBold(true)
                            ->getColor()
                            ->setRGB('f59e0b'); // Amber-500
                    } else {
                        // Blue for attention
                        $sheet->getStyle($statusCell)
                            ->getFont()
                            ->setBold(true)
                            ->getColor()
                            ->setRGB('3b82f6'); // Blue-500
                    }
                    
                    $rowIndex++;
                }

                // Center align
                $sheet->getStyle("C2:G{$highestRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("I2:I{$highestRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

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
