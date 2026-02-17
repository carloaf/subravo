@extends('layouts.pdf')

@section('title', $reportTitle)
@section('report-title', $reportTitle)
@section('meta-right', '<strong>Produtos abaixo do mínimo:</strong> ' . $products->count())

@section('content')

{{-- Cards de Resumo --}}
@php
    $totalDeficit = $products->sum(function($p) {
        return $p->minimum_stock - $p->getAvailableStock();
    });
    $critical = $products->filter(fn($p) => $p->getAvailableStock() == 0)->count();
@endphpphp

<table class="summary">
    <tr>
        <td style="background: #fef2f2; border-left: 4px solid #ef4444;">
            <div class="label">⚠ Produtos em Falta</div>
            <div class="value text-red">{{ $products->count() }}</div>
        </td>
        <td style="background: #fef2f2; border-left: 4px solid #dc2626;">
            <div class="label">🚨 Críticos (Zero)</div>
            <div class="value text-red">{{ $critical }}</div>
        </td>
        <td style="background: #fff7ed; border-left: 4px solid #f59e0b;">
            <div class="label">📋 Déficit Total</div>
            <div class="value text-amber">{{ $totalDeficit }}</div>
        </td>
    </tr>
</table>

@if($products->isNotEmpty())
<div class="alert alert-danger">
    <strong>🚨 ALERTA CRÍTICO:</strong> {{ $products->count() }} produto(s) com estoque abaixo do mínimo. {{ $critical > 0 ? "Atenção especial para {$critical} produto(s) com estoque ZERADO." : '' }} Solicitar reposição urgente.
</div>
@endif

<div class="section-title" style="border-left-color: #ef4444; background: #fef2f2; color: #991b1b;">
    📋 Produtos Críticos - Requisição Urgente
</div>

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%">#</th>
            <th style="width: 28%">Produto</th>
            <th style="width: 15%">Categoria</th>
            <th style="width: 8%">Unid.</th>
            <th style="width: 10%">Disponível</th>
            <th style="width: 10%">Mínimo</th>
            <th style="width: 10%">Déficit</th>
            <th style="width: 15%">Criticidade</th>
        </tr>
    </thead>
    <tbody>
        @foreach($products->sortByDesc(fn($p) => $p->minimum_stock - $p->getAvailableStock()) as $i => $product)
            @php
                $available = $product->getAvailableStock();
                $deficit = $product->minimum_stock - $available;
                $isCritical = $available == 0;
                $percentage = $product->minimum_stock > 0 ? round(($available / $product->minimum_stock) * 100) : 0;
            @endphp
            <tr class="{{ $isCritical ? 'bg-red-light' : 'bg-amber-light' }}">
                <td class="center" style="color: #9ca3af;">{{ $i + 1 }}</td>
                <td class="left bold">{{ $product->name }}</td>
                <td class="left" style="color: #6b7280;">{{ $product->category?->name ?? '—' }}</td>
                <td class="center">{{ $product->unit }}</td>
                <td class="center bold {{ $isCritical ? 'text-red' : 'text-amber' }}">{{ $available }}</td>
                <td class="center" style="color: #6b7280;">{{ $product->minimum_stock }}</td>
                <td class="center bold text-red">-{{ $deficit }}</td>
                <td class="center">
                    @if($isCritical)
                        <span class="badge badge-red">🚨 ZERADO</span>
                    @elseif($percentage <= 25)
                        <span class="badge badge-red">⚠ CRÍTICO</span>
                    @else
                        <span class="badge badge-amber">⚠ BAIXO</span>
                    @endif
                    <div style="margin-top: 4px;">
                        <div class="progress" style="height: 8px; background: #fee2e2;">
                            <div class="progress-bar" style="background: {{ $isCritical ? '#dc2626' : '#f59e0b' }}; width: {{ $percentage }}%;"></div>
                        </div>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="right" style="font-size: 10px;">TOTAIS:</td>
            <td class="center bold text-red" style="font-size: 11px;">—</td>
            <td class="center" style="font-size: 11px;">—</td>
            <td class="center bold text-red" style="font-size: 11px;">{{ $totalDeficit }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>

@if($products->isEmpty())
    <div class="alert alert-info" style="margin-top: 20px;">
        <strong>✓ Situação Normal:</strong> Nenhum produto abaixo do estoque mínimo. Todos os itens estão com níveis adequados de estoque.
    </div>
@endif

@endsection
