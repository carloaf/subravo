@extends('layouts.pdf')

@section('title', $reportTitle)
@section('report-title', $reportTitle)
@section('meta-right', '<strong>Itens com validade próxima:</strong> ' . $items->count())

@section('content')

{{-- Cards de Resumo --}}
@php
    $expired = $items->filter(fn($i) => $i->expiration_date < now())->count();
    $expiring15 = $items->filter(function($i) {
        $days = now()->diffInDays($i->expiration_date, false);
        return $days >= 0 && $days <= 15;
    })->count();
    $expiring30 = $items->filter(function($i) {
        $days = now()->diffInDays($i->expiration_date, false);
        return $days > 15 && $days <= 30;
    })->count();
    $expiring60 = $items->filter(function($i) {
        $days = now()->diffInDays($i->expiration_date, false);
        return $days > 30 && $days <= 60;
    })->count();
@endphp

<table class="summary">
    <tr>
        <td style="background: #fef2f2; border-left: 4px solid #dc2626;">
            <div class="label">❌ Vencidos</div>
            <div class="value text-red">{{ $expired }}</div>
        </td>
        <td style="background: #fef3c7; border-left: 4px solid #f59e0b;">
            <div class="label">⚠ 15 dias</div>
            <div class="value text-amber">{{ $expiring15 }}</div>
        </td>
        <td style="background: #fff7ed; border-left: 4px solid #fb923c;">
            <div class="label">🕒 30 dias</div>
            <div class="value" style="color: #ea580c;">{{ $expiring30 }}</div>
        </td>
        <td style="background: #dbeafe; border-left: 4px solid #3b82f6;">
            <div class="label">📅 60 dias</div>
            <div class="value text-blue">{{ $expiring60 }}</div>
        </td>
    </tr>
</table>

@if($expired > 0)
<div class="alert alert-danger">
    <strong>🚨 ATENÇÃO URGENTE:</strong> Existem {{ $expired }} item(ns) com validade VENCIDA. Realizar descarte/recolhimento imediato conforme regulamento.
</div>
@elseif($expiring15 > 0)
<div class="alert alert-warning">
    <strong>⚠ ALERTA:</strong> {{ $expiring15 }} item(ns) vencendo nos próximos 15 dias. Ação necessária: priorizar uso ou planejar descarte.
</div>
@endif

{{-- Seção de Vencidos --}}
@php
    $expiredItems = $items->filter(fn($i) => $i->expiration_date < now())->sortBy('expiration_date');
@endphp

@if($expiredItems->isNotEmpty())
<div class="section-title" style="border-left-color: #dc2626; background: #fef2f2; color: #991b1b;">
    ❌ Itens Vencidos - Requisição de Descarte Urgente
</div>

<table class="data-table">
    <thead>
        <tr style="background: linear-gradient(135deg, #b91c1c, #dc2626);">
            <th style="width: 4%">#</th>
            <th style="width: 28%">Produto</th>
            <th style="width: 12%">Lote</th>
            <th style="width: 12%">Localização</th>
            <th style="width: 8%">Qtd.</th>
            <th style="width: 12%">Vencimento</th>
            <th style="width: 12%">Dias Vencido</th>
            <th style="width: 12%">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($expiredItems as $j => $item)
            @php
                $daysExpired = abs((int) now()->diffInDays($item->expiration_date, false));
            @endphp
            <tr class="bg-red-light">
                <td class="center" style="color: #9ca3af;">{{ $j + 1 }}</td>
                <td class="left bold">{{ $item->product->name }}</td>
                <td class="center" style="font-family: 'Courier New'; font-size: 9px;">{{ $item->batch ?? '—' }}</td>
                <td class="left" style="font-size: 9px; color: #6b7280;">{{ $item->location ?? '—' }}</td>
                <td class="center bold text-red">{{ $item->quantity }}</td>
                <td class="center">{{ $item->expiration_date->format('d/m/Y') }}</td>
                <td class="center bold text-red">{{ $daysExpired }}d</td>
                <td class="center">
                    <span class="badge badge-red">❌ VENCIDO</span>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- Seção de Itens Próximos do Vencimento --}}
@php
    $activeItems = $items->filter(fn($i) => $i->expiration_date >= now())->sortBy('expiration_date');
@endphp

@if($activeItems->isNotEmpty())
<div class="section-title" style="border-left-color: #f59e0b; background: #fef3c7; color: #92400e; margin-top: 20px;">
    📅 Itens Próximos do Vencimento (0-60 dias)
</div>

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%">#</th>
            <th style="width: 28%">Produto</th>
            <th style="width: 12%">Lote</th>
            <th style="width: 12%">Localização</th>
            <th style="width: 8%">Qtd.</th>
            <th style="width: 12%">Vencimento</th>
            <th style="width: 12%">Dias Restantes</th>
            <th style="width: 12%">Urgência</th>
        </tr>
    </thead>
    <tbody>
        @foreach($activeItems as $i => $item)
            @php
                $daysRemaining = (int) now()->diffInDays($item->expiration_date, false);
                $urgency = match(true) {
                    $daysRemaining <= 15 => ['class' => 'bg-amber-light', 'badge' => 'badge-red', 'color' => '#dc2626', 'label' => '⚠ CRÍTICO', 'icon' => '⚠'],
                    $daysRemaining <= 30 => ['class' => '', 'badge' => 'badge-amber', 'color' => '#f59e0b', 'label' => '⚠ ALTO', 'icon' => '🕒'],
                    default => ['class' => '', 'badge' => 'badge-blue', 'color' => '#3b82f6', 'label' => '📅 MÉDIO', 'icon' => '📅']
                };
                $percentage = min(100, ($daysRemaining / 60) * 100);
            @endphp
            <tr class="{{ $urgency['class'] }}">
                <td class="center" style="color: #9ca3af;">{{ $i + 1 }}</td>
                <td class="left bold">{{ $item->product->name }}</td>
                <td class="center" style="font-family: 'Courier New'; font-size: 9px;">{{ $item->batch ?? '—' }}</td>
                <td class="left" style="font-size: 9px; color: #6b7280;">{{ $item->location ?? '—' }}</td>
                <td class="center bold" style="color: {{ $urgency['color'] }};">{{ $item->quantity }}</td>
                <td class="center">{{ $item->expiration_date->format('d/m/Y') }}</td>
                <td class="center bold" style="color: {{ $urgency['color'] }};">{{ $urgency['icon'] }} {{ $daysRemaining }}d</td>
                <td class="center">
                    <span class="{{ $urgency['badge'] }}">{{ $urgency['label'] }}</span>
                    <div style="margin-top: 4px;">
                        <div class="progress" style="height: 6px; background: #f3f4f6;">
                            <div class="progress-bar" style="background: {{ $urgency['color'] }}; width: {{ $percentage }}%;"></div>
                        </div>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif

@if($items->isEmpty())
    <div class="alert alert-info" style="margin-top: 20px;">
        <strong>✓ Situação Normal:</strong> Nenhum item próximo do vencimento (60 dias). Todas as validades estão dentro do período seguro.
    </div>
@endif

@endsection
