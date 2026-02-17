@extends('layouts.pdf')

@section('title', $reportTitle)
@section('report-title', $reportTitle)
@section('meta-right', '<strong>Total de cautelas:</strong> ' . $loans->count())

@section('content')

{{-- Cards de Resumo --}}
@php
    $overdue = $loans->filter(fn($l) => $l->expected_return_date && $l->expected_return_date->isPast())->count();
    $totalItems = $loans->sum(fn($l) => $l->items->count());
@endphp

<table class="summary">
    <tr>
        <td style="background: #fffbeb; border-left: 4px solid #f59e0b;">
            <div class="label">📋 Cautelas Ativas</div>
            <div class="value text-amber">{{ $loans->count() }}</div>
        </td>
        <td style="background: #fef2f2; border-left: 4px solid #ef4444;">
            <div class="label">⏰ Atrasadas</div>
            <div class="value text-red">{{ $overdue }}</div>
        </td>
        <td style="background: #eff6ff; border-left: 4px solid #3b82f6;">
            <div class="label">📦 Total de Itens</div>
            <div class="value text-blue">{{ $totalItems }}</div>
        </td>
        <td style="background: #f0fdf4; border-left: 4px solid #059669;">
            <div class="label">✓ No Prazo</div>
            <div class="value text-green">{{ $loans->count() - $overdue }}</div>
        </td>
    </tr>
</table>

@if($overdue > 0)
<div class="alert alert-danger">
    <strong>⚠ ATENÇÃO URGENTE:</strong> {{ $overdue }} cautela(s) está(ão) com devolução atrasada. Providenciar contato imediato com os mutuários.
</div>
@endif

<div class="section-title">📄 Detalhamento das Cautelas Ativas</div>

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%">#</th>
            <th style="width: 13%">Nº Cautela</th>
            <th style="width: 20%">Mutuário</th>
            <th style="width: 9%">OM</th>
            <th style="width: 9%">Data</th>
            <th style="width: 10%">Prev. Devol.</th>
            <th style="width: 7%">Itens</th>
            <th style="width: 15%">Expedido por</th>
            <th style="width: 13%">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($loans as $i => $loan)
            @php 
                $overdue = $loan->expected_return_date && $loan->expected_return_date->isPast();
                $daysLate = $overdue ? $loan->expected_return_date->diffInDays(now()) : 0;
            @endphp
            <tr class="{{ $overdue ? 'bg-red-light' : '' }}">
                <td class="center" style="color: #9ca3af;">{{ $i + 1 }}</td>
                <td class="left bold" style="font-family: monospace;">{{ $loan->loan_number }}</td>
                <td class="left">
                    @if($loan->borrower_type === 'individual')
                        {{ $loan->borrower?->rank?->abbreviation }} {{ $loan->borrower?->war_name ?? '—' }}
                    @else
                        <strong>{{ $loan->borrower_section }}</strong>
                    @endif
                </td>
                <td class="center" style="color: #6b7280;">{{ $loan->borrowerOrganization?->abbreviation ?? '—' }}</td>
                <td class="center">{{ $loan->loan_date->format('d/m/Y') }}</td>
                <td class="center {{ $overdue ? 'text-red bold' : '' }}">
                    {{ $loan->expected_return_date?->format('d/m/Y') ?? '—' }}
                    @if($overdue)
                        <br><span style="font-size: 7px;">({{ $daysLate }} dias)</span>
                    @endif
                </td>
                <td class="center bold text-blue">{{ $loan->items->count() }}</td>
                <td class="left" style="color: #6b7280;">{{ $loan->loanedBy?->war_name ?? '—' }}</td>
                <td class="center">
                    @if($overdue)
                        <span class="badge badge-red">⏰ ATRASADA</span>
                    @else
                        <span class="badge badge-amber">📋 ATIVA</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" class="right" style="font-size: 10px;">TOTAIS:</td>
            <td class="center bold text-blue" style="font-size: 11px;">{{ $totalItems }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>

@if($overdue > 0)
<div class="section-title" style="border-left-color: #ef4444; background: #fef2f2; color: #991b1b;">
    🚨 Lista de Cautelas Atrasadas (Ação Imediata Necessária)
</div>

<table class="data-table">
    <thead>
        <tr style="background: linear-gradient(180deg, #dc2626 0%, #ef4444 100%);">
            <th>Nº Cautela</th>
            <th>Mutuário</th>
            <th>Prazo</th>
            <th>Dias Atraso</th>
            <th>Itens</th>
        </tr>
    </thead>
    <tbody>
        @foreach($loans->filter(fn($l) => $l->expected_return_date && $l->expected_return_date->isPast()) as $loan)
            <tr class="bg-red-light">
                <td class="left bold">{{ $loan->loan_number }}</td>
                <td class="left">
                    @if($loan->borrower_type === 'individual')
                        {{ $loan->borrower?->rank?->abbreviation }} {{ $loan->borrower?->war_name }}
                    @else
                        {{ $loan->borrower_section }}
                    @endif
                </td>
                <td class="center">{{ $loan->expected_return_date->format('d/m/Y') }}</td>
                <td class="center bold text-red">{{ $loan->expected_return_date->diffInDays(now()) }}</td>
                <td class="center">{{ $loan->items->count() }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif

@endsection
