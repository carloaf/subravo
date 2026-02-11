<?php

namespace App\Exports;

use App\Models\StockItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpirationAlertExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
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
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4a5d23'],
                ],
            ],
        ];
    }
}
