@extends('layouts.pdf')

@section('title', $reportTitle)
@section('report-title', $reportTitle)
@section('meta-right')
    @if($dateFrom && $dateTo)
        Período: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
    @else
        Todos os registros
    @endif
    &mdash; {{ $loans->count() }} cautelas
@endsection

@section('content')

@php
    $statusColors = ['active' => 'amber', 'returned' => 'green', 'partial' => 'blue', 'overdue' => 'red'];
    $statusLabels = ['active' => 'Ativa', 'returned' => 'Devolvida', 'partial' => 'Parcial', 'overdue' => 'Atrasada'];
    
    // Estatísticas
    $returned = $loans->where('status', 'returned')->count();
    $active = $loans->where('status', 'active')->count();
    $overdue = $loans->where('status', 'overdue')->count();
    $partial = $loans->where('status', 'partial')->count();
    $totalLoans = $loans->count();
@endphp

{{-- Cards de Resumo --}}
<table class="summary">
    <tr>
        <td style="background: #d1fae5; border-left: 4px solid #10b981;">
            <div class="label">✓ Devolvidas</div>
            <div class="value text-green">{{ $returned }}</div>
            <div style="font-size: 8px; color: #6b7280; margin-top: 2px;">{{ $totalLoans > 0 ? round(($returned/$totalLoans)*100) : 0 }}%</div>
        </td>
        <td style="background: #fef3c7; border-left: 4px solid #f59e0b;">
            <div class="label">📋 Ativas</div>
            <div class="value text-amber">{{ $active }}</div>
        </td>
        <td style="background: #fecaca; border-left: 4px solid #ef4444;">
            <div class="label">⏰ Atrasadas</div>
            <div class="value text-red">{{ $overdue }}</div>
        </td>
        <td style="background: #dbeafe; border-left: 4px solid #3b82f6;">
            <div class="label">📊 Total</div>
            <div class="value text-blue">{{ $totalLoans }}</div>
        </td>
    </tr>
</table>

@if($overdue > 0)
<div class="alert alert-warning">
    <strong>⚠ ALERTAS:</strong> Existem {{ $overdue }} cautela(s) atrasada(s) no período. Verificar necessidade de cobrança.
</div>
@endif

<div class="section-title" style="border-left-color: #3b82f6; background: #dbeafe; color: #1e40af;">
    📜 Histórico de Cautelas
</div>

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%">#</th>
            <th style="width: 14%">Nº Cautela</th>
            <th style="width: 22%">Mutuário</th>
            <th style="width: 8%">OM</th>
            <th style="width: 10%">Emissão</th>
            <th style="width: 10%">Devolução</th>
            <th style="width: 8%">Dias</th>
            <th style="width: 12%">Expedido</th>
            <th style="width: 12%">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($loans as $i => $loan)
            @php
                $daysActive = $loan->actual_return_date 
                    ? $loan->loan_date->diffInDays($loan->actual_return_date)
                    : $loan->loan_date->diffInDays(now());
                $isOverdue = $loan->status === 'overdue';
            @endphp
            <tr class="{{ $isOverdue ? 'bg-red-light' : '' }}">
                <td class="center" style="color: #9ca3af;">{{ $i + 1 }}</td>
                <td class="left bold" style="font-family: 'Courier New'; font-size: 9px;">{{ $loan->loan_number }}</td>
                <td class="left" style="font-size: 9px;">
                    @if($loan->borrower_type === 'individual')
                        <span style="color: #6b7280;">{{ $loan->borrower?->rank?->abbreviation }}</span> {{ $loan->borrower?->war_name ?? '—' }}
                    @else
                        <span style="font-weight: bold;">{{ $loan->borrower_section }}</span>
                    @endif
                </td>
                <td class="center" style="font-size: 9px;">{{ $loan->borrowerOrganization?->abbreviation ?? '—' }}</td>
                <td class="center" style="font-size: 9px;">{{ $loan->loan_date->format('d/m/Y') }}</td>
                <td class="center" style="font-size: 9px;">
                    @if($loan->actual_return_date)
                        {{ $loan->actual_return_date->format('d/m/Y') }}
                    @else
                        <span style="color: #9ca3af;">—</span>
                    @endif
                </td>
                <td class="center" style="font-size: 9px; color: #6b7280;">{{ $daysActive }}d</td>
                <td class="center" style="font-size: 9px;">{{ $loan->loanedBy?->war_name ?? '—' }}</td>
                <td class="center">
                    @php
                        $icons = ['active' => '📋', 'returned' => '✓', 'partial' => '◐', 'overdue' => '⏰'];
                    @endphp
                    <span class="badge badge-{{ $statusColors[$loan->status] ?? 'gray' }}">
                        {{ $icons[$loan->status] ?? '' }} {{ $statusLabels[$loan->status] ?? ucfirst($loan->status) }}
                    </span>
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="8" class="right" style="font-size: 10px;">TOTAIS:</td>
            <td class="center" style="font-size: 10px;">
                <span class="text-green bold">{{ $returned }}</span> / 
                <span class="text-amber bold">{{ $active }}</span> /
                <span class="text-red bold">{{ $overdue }}</span>
            </td>
        </tr>
    </tfoot>
</table>

@endsection
