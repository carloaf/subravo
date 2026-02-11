<?php

namespace App\Exports;

use App\Models\StockMovement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MovementReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
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
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4a5d23'],
                ],
            ],
        ];
    }
}
