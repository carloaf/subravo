<?php

namespace App\Exports;

use App\Models\Loan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LoanReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
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
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4a5d23'],
                ],
            ],
        ];
    }
}
