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
@endphp

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 5%">#</th>
            <th style="width: 12%">Data</th>
            <th style="width: 25%">Produto</th>
            <th style="width: 12%">Tipo</th>
            <th style="width: 8%">Qtd</th>
            <th style="width: 15%">Responsável</th>
            <th style="width: 23%">Motivo / Observação</th>
        </tr>
    </thead>
    <tbody>
        @foreach($movements as $i => $mov)
            @php $isPositive = in_array($mov->movement_type, ['entry', 'return']); @endphp
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td class="center">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                <td class="left">{{ $mov->stockItem?->product?->name ?? '—' }}</td>
                <td class="center">
                    <span class="badge badge-{{ $typeBadges[$mov->movement_type] ?? 'gray' }}">
                        {{ $typeLabels[$mov->movement_type] ?? $mov->movement_type }}
                    </span>
                </td>
                <td class="center bold {{ $isPositive ? 'text-green' : 'text-red' }}">
                    {{ $isPositive ? '+' : '-' }}{{ $mov->quantity }}
                </td>
                <td class="center">{{ $mov->performedBy?->war_name ?? '—' }}</td>
                <td class="left" style="font-size: 8px;">{{ Str::limit($mov->notes, 60) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="right">Total de Movimentações:</td>
            <td class="center bold">{{ $movements->count() }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>

@endsection
