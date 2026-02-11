@extends('layouts.pdf')

@section('title', $reportTitle)
@section('report-title', $reportTitle)
@section('meta-right', $products->count() . ' produtos abaixo do mínimo')

@section('content')

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 5%">#</th>
            <th style="width: 30%">Produto</th>
            <th style="width: 20%">Categoria</th>
            <th style="width: 10%">Unid.</th>
            <th style="width: 10%">Disponível</th>
            <th style="width: 10%">Mínimo</th>
            <th style="width: 15%">Déficit</th>
        </tr>
    </thead>
    <tbody>
        @foreach($products as $i => $product)
            @php
                $available = $product->getAvailableStock();
                $deficit = $product->minimum_stock - $available;
            @endphp
            <tr class="bg-red-light">
                <td class="center">{{ $i + 1 }}</td>
                <td class="left bold">{{ $product->name }}</td>
                <td class="left">{{ $product->category?->name ?? '—' }}</td>
                <td class="center">{{ $product->unit }}</td>
                <td class="center bold text-red">{{ $available }}</td>
                <td class="center">{{ $product->minimum_stock }}</td>
                <td class="center">
                    <span class="badge badge-red">-{{ $deficit }}</span>
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" class="right">Total de Produtos em Falta:</td>
            <td class="center bold text-red">{{ $products->count() }}</td>
        </tr>
    </tfoot>
</table>

@if($products->isEmpty())
    <p style="text-align: center; padding: 20px; color: #16a34a; font-weight: bold; font-size: 12px;">
        ✓ Nenhum produto abaixo do estoque mínimo. Estoque adequado.
    </p>
@endif

@endsection
