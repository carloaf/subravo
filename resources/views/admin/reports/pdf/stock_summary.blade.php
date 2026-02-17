@extends('layouts.pdf')

@section('title', $reportTitle)
@section('report-title', $reportTitle)
@section('meta-right', '<strong>Total de produtos:</strong> ' . $products->count())

@section('content')

{{-- Cards de Resumo --}}
<table class="summary">
    <tr>
        <td style="background: #f0fdf4; border-left: 4px solid #059669;">
            <div class="label">✓ Total Disponível</div>
            <div class="value text-green">{{ $totalAvailable }}</div>
        </td>
        <td style="background: #fffbeb; border-left: 4px solid #f59e0b;">
            <div class="label">⊙ Total Emprestado</div>
            <div class="value text-amber">{{ $totalLoaned }}</div>
        </td>
        <td style="background: #fef2f2; border-left: 4px solid #ef4444;">
            <div class="label">⚠ Abaixo do Mínimo</div>
            <div class="value text-red">{{ $products->where('below_minimum', true)->count() }}</div>
        </td>
        <td style="background: #eff6ff; border-left: 4px solid #3b82f6;">
            <div class="label">Σ Total Itens</div>
            <div class="value text-blue">{{ $totalAvailable + $totalLoaned }}</div>
        </td>
    </tr>
</table>

@if($products->where('below_minimum', true)->count() > 0)
<div class="alert alert-warning">
    <strong>⚠ Atenção:</strong> {{ $products->where('below_minimum', true)->count() }} produto(s) está(ão) com estoque abaixo do mínimo recomendado.
</div>
@endif

<div class="section-title">📦 Detalhamento por Produto</div>

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%">#</th>
            <th style="width: 28%">Produto</th>
            <th style="width: 14%">Categoria</th>
            <th style="width: 7%">Unid.</th>
            <th style="width: 10%">Disponível</th>
            <th style="width: 10%">Emprestado</th>
            <th style="width: 9%">Total</th>
            <th style="width: 9%">Mínimo</th>
            <th style="width: 9%">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($products as $i => $prod)
            <tr class="{{ $prod['below_minimum'] ? 'bg-red-light' : '' }}">
                <td class="center" style="color: #9ca3af;">{{ $i + 1 }}</td>
                <td class="left bold">{{ $prod['name'] }}</td>
                <td class="left" style="color: #6b7280;">{{ $prod['category'] }}</td>
                <td class="center">{{ $prod['unit'] }}</td>
                <td class="center bold text-green">{{ $prod['available'] }}</td>
                <td class="center bold text-amber">{{ $prod['loaned'] }}</td>
                <td class="center" style="color: #6b7280;">{{ $prod['available'] + $prod['loaned'] }}</td>
                <td class="center" style="color: #9ca3af;">{{ $prod['minimum'] }}</td>
                <td class="center">
                    @if($prod['below_minimum'])
                        <span class="badge badge-red">⚠ CRÍTICO</span>
                    @else
                        <span class="badge badge-green">✓ OK</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="right" style="font-size: 10px;">TOTAIS GERAIS:</td>
            <td class="center bold text-green" style="font-size: 11px;">{{ $totalAvailable }}</td>
            <td class="center bold text-amber" style="font-size: 11px;">{{ $totalLoaned }}</td>
            <td class="center bold text-blue" style="font-size: 11px;">{{ $totalAvailable + $totalLoaned }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>

@endsection
