@extends('layouts.pdf')

@section('title', $reportTitle)
@section('report-title', $reportTitle)
@section('meta-right', $products->count() . ' produtos')

@section('content')

{{-- Totais --}}
<table class="summary">
    <tr>
        <td>
            <div class="label">Total Disponível</div>
            <div class="value text-green">{{ $totalAvailable }}</div>
        </td>
        <td>
            <div class="label">Total Emprestado</div>
            <div class="value text-amber">{{ $totalLoaned }}</div>
        </td>
        <td>
            <div class="label">Abaixo do Mínimo</div>
            <div class="value text-red">{{ $products->where('below_minimum', true)->count() }}</div>
        </td>
    </tr>
</table>

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 5%">#</th>
            <th style="width: 30%">Produto</th>
            <th style="width: 15%">Categoria</th>
            <th style="width: 8%">Unid.</th>
            <th style="width: 10%">Disponível</th>
            <th style="width: 10%">Emprestado</th>
            <th style="width: 10%">Mínimo</th>
            <th style="width: 12%">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($products as $i => $prod)
            <tr class="{{ $prod['below_minimum'] ? 'bg-red-light' : '' }}">
                <td class="center">{{ $i + 1 }}</td>
                <td class="left">{{ $prod['name'] }}</td>
                <td class="left">{{ $prod['category'] }}</td>
                <td class="center">{{ $prod['unit'] }}</td>
                <td class="center bold text-green">{{ $prod['available'] }}</td>
                <td class="center text-amber">{{ $prod['loaned'] }}</td>
                <td class="center">{{ $prod['minimum'] }}</td>
                <td class="center">
                    @if($prod['below_minimum'])
                        <span class="badge badge-red">ABAIXO</span>
                    @else
                        <span class="badge badge-green">OK</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="right">Totais:</td>
            <td class="center bold text-green">{{ $totalAvailable }}</td>
            <td class="center bold text-amber">{{ $totalLoaned }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>

@endsection
