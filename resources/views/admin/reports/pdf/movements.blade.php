@extends('layouts.pdf')

@section('title', $reportTitle)
@section('report-title', $reportTitle)
@section('meta-right')
    @if($dateFrom && $dateTo)
        Período: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
    @else
        Todos os registros
    @endif
    &mdash; {{ $movements->count() }} movimentações
@endsection

@section('content')

@php
    $typeLabels = [
        'entry' => 'Entrada', 'exit' => 'Saída', 'loan' => 'Empréstimo',
        'return' => 'Devolução', 'adjustment' => 'Ajuste', 'decommission' => 'Baixa',
    ];
    $typeBadges = [
        'entry' => 'green', 'exit' => 'red', 'loan' => 'amber',
        'return' => 'blue', 'adjustment' => 'purple', 'decommission' => 'gray',
    ];
    
    // Estatísticas
    $entries = $movements->whereIn('movement_type', ['entry', 'return'])->count();
    $exits = $movements->whereIn('movement_type', ['exit', 'loan'])->count();
    $adjustments = $movements->where('movement_type', 'adjustment')->count();
    $totalMovements = $movements->count();
    
    $totalQtyIn = $movements->whereIn('movement_type', ['entry', 'return'])->sum('quantity');
    $totalQtyOut = $movements->whereIn('movement_type', ['exit', 'loan'])->sum('quantity');
@endphp

{{-- Cards de Resumo --}}
<table class="summary">
    <tr>
        <td style="background: #d1fae5; border-left: 4px solid #10b981;">
            <div class="label">📈 Entradas</div>
            <div class="value text-green">{{ $entries }}</div>
            <div style="font-size: 8px; color: #6b7280; margin-top: 2px;">+{{ $totalQtyIn }} unidades</div>
        </td>
        <td style="background: #fecaca; border-left: 4px solid #ef4444;">
            <div class="label">📉 Saídas</div>
            <div class="value text-red">{{ $exits }}</div>
            <div style="font-size: 8px; color: #6b7280; margin-top: 2px;">-{{ $totalQtyOut }} unidades</div>
        </td>
        <td style="background: #e0e7ff; border-left: 4px solid #6366f1;">
            <div class="label">⚙ Ajustes</div>
            <div class="value" style="color: #6366f1;">{{ $adjustments }}</div>
        </td>
        <td style="background: #dbeafe; border-left: 4px solid #3b82f6;">
            <div class="label">📋 Total</div>
            <div class="value text-blue">{{ $totalMovements }}</div>
        </td>
    </tr>
</table>

<div class="section-title" style="border-left-color: #3b82f6; background: #dbeafe; color: #1e40af;">
    📋 Histórico de Movimentações
</div>

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%">#</th>
            <th style="width: 12%">Data/Hora</th>
            <th style="width: 26%">Produto</th>
            <th style="width: 12%">Tipo</th>
            <th style="width: 8%">Qtd.</th>
            <th style="width: 15%">Responsável</th>
            <th style="width: 23%">Observações</th>
        </tr>
    </thead>
    <tbody>
        @foreach($movements as $i => $mov)
            @php 
                $isPositive = in_array($mov->movement_type, ['entry', 'return']); 
                $icons = [
                    'entry' => '📦', 'exit' => '📤', 'loan' => '🤝',
                    'return' => '↩️', 'adjustment' => '⚙', 'decommission' => '🗑️',
                ];
            @endphp
            <tr>
                <td class="center" style="color: #9ca3af;">{{ $i + 1 }}</td>
                <td class="center" style="font-size: 9px;">
                    <div>{{ $mov->created_at->format('d/m/Y') }}</div>
                    <div style="color: #9ca3af; font-size: 7px;">{{ $mov->created_at->format('H:i') }}</div>
                </td>
                <td class="left bold">{{ $mov->stockItem?->product?->name ?? '—' }}</td>
                <td class="center">
                    <span class="badge badge-{{ $typeBadges[$mov->movement_type] ?? 'gray' }}">
                        {{ $icons[$mov->movement_type] ?? '' }} {{ $typeLabels[$mov->movement_type] ?? $mov->movement_type }}
                    </span>
                </td>
                <td class="center bold {{ $isPositive ? 'text-green' : 'text-red' }}" style="font-size: 11px;">
                    {{ $isPositive ? '+' : '-' }}{{ $mov->quantity }}
                </td>
                <td class="center" style="font-size: 9px;">{{ $mov->performedBy?->war_name ?? '—' }}</td>
                <td class="left" style="font-size: 8px; color: #6b7280;">{{ Str::limit($mov->notes, 60) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="right" style="font-size: 10px;">TOTAIS:</td>
            <td colspan="3" class="left" style="font-size: 10px;">
                <span class="text-green bold">+{{ $totalQtyIn }}</span> / 
                <span class="text-red bold">-{{ $totalQtyOut }}</span> 
                <span style="color: #9ca3af;">({{ $totalMovements }} movimentações)</span>
            </td>
        </tr>
    </tfoot>
</table>

@endsection
