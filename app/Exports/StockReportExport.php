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
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
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
