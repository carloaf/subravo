<?php

namespace App\Exports;

use App\Models\Loan;
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

class LoanReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    public function __construct(
        protected string $scope = 'all',
        protected ?string $dateFrom = null,
        protected ?string $dateTo = null,
    ) {}

    public function collection()
    {
        $query = Loan::with(['borrower.rank', 'borrowerOrganization', 'loanedBy.rank', 'items.stockItem.product']);

        if ($this->scope === 'active') {
            $query->active();
        }

        if ($this->dateFrom && $this->dateTo) {
            $query->forDateRange($this->dateFrom, $this->dateTo);
        }

        return $query->latest('loan_date')->get();
    }

    public function headings(): array
    {
        return [
            'Nº Cautela',
            'Tipo',
            'Mutuário',
            'OM',
            'Data Emissão',
            'Prev. Devolução',
            'Data Devolução',
            'Expedido por',
            'Qtd Itens',
            'Status',
        ];
    }

    public function map($loan): array
    {
        $statusLabels = [
            'active' => 'Ativa', 'returned' => 'Devolvida',
            'partial' => 'Parcial', 'overdue' => 'Atrasada',
        ];

        $borrowerName = $loan->borrower_type === 'individual'
            ? ($loan->borrower?->rank?->abbreviation . ' ' . $loan->borrower?->war_name)
            : $loan->borrower_section;

        return [
            $loan->loan_number,
            $loan->borrower_type === 'individual' ? 'Individual' : 'Seção',
            $borrowerName ?? '—',
            $loan->borrowerOrganization?->abbreviation ?? '—',
            $loan->loan_date->format('d/m/Y'),
            $loan->expected_return_date?->format('d/m/Y') ?? '—',
            $loan->actual_return_date?->format('d/m/Y') ?? '—',
            $loan->loanedBy?->war_name ?? '—',
            $loan->items->count(),
            $statusLabels[$loan->status] ?? ucfirst($loan->status),
        ];
    }

    public function title(): string
    {
        return $this->scope === 'active' ? 'Cautelas Ativas' : 'Histórico de Cautelas';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row - Emerald
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
                $loans = $this->collection();

                // Freeze first row
                $sheet->freezePane('A2');

                // Zebra striping
                for ($row = 2; $row <= $highestRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle("A{$row}:{$highestColumn}{$row}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('f0fdf4'); // Green-50
                    }
                }

                // Conditional formatting based on status (column J)
                $rowIndex = 2;
                foreach ($loans as $loan) {
                    $statusCell = "J{$rowIndex}";
                    $status = $loan->status;
                    
                    if ($status === 'overdue') {
                        // Red background for overdue loans
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
                    } elseif ($status === 'active') {
                        // Amber for active
                        $sheet->getStyle($statusCell)
                            ->getFont()
                            ->setBold(true)
                            ->getColor()
                            ->setRGB('f59e0b'); // Amber-500
                    } elseif ($status === 'returned') {
                        // Green for returned
                        $sheet->getStyle($statusCell)
                            ->getFont()
                            ->setBold(true)
                            ->getColor()
                            ->setRGB('059669'); // Emerald-600
                    } else {
                        // Blue for partial
                        $sheet->getStyle($statusCell)
                            ->getFont()
                            ->setBold(true)
                            ->getColor()
                            ->setRGB('3b82f6'); // Blue-500
                    }
                    
                    $rowIndex++;
                }

                // Center align specific columns
                $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D2:G{$highestRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("I2:J{$highestRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Add borders
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
