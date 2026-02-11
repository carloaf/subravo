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

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 5%">#</th>
            <th style="width: 15%">Nº Cautela</th>
            <th style="width: 22%">Mutuário</th>
            <th style="width: 10%">OM</th>
            <th style="width: 10%">Emissão</th>
            <th style="width: 10%">Devolução</th>
            <th style="width: 10%">Expedido por</th>
            <th style="width: 8%">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($loans as $i => $loan)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td class="left bold">{{ $loan->loan_number }}</td>
                <td class="left">
                    @if($loan->borrower_type === 'individual')
                        {{ $loan->borrower?->rank?->abbreviation }} {{ $loan->borrower?->war_name ?? '—' }}
                    @else
                        {{ $loan->borrower_section }}
                    @endif
                </td>
                <td class="center">{{ $loan->borrowerOrganization?->abbreviation ?? '—' }}</td>
                <td class="center">{{ $loan->loan_date->format('d/m/Y') }}</td>
                <td class="center">{{ $loan->actual_return_date?->format('d/m/Y') ?? '—' }}</td>
                <td class="center">{{ $loan->loanedBy?->war_name ?? '—' }}</td>
                <td class="center">
                    @php
                        $colors = ['active' => 'amber', 'returned' => 'green', 'partial' => 'blue', 'overdue' => 'red'];
                        $labels = ['active' => 'Ativa', 'returned' => 'Devolvida', 'partial' => 'Parcial', 'overdue' => 'Atrasada'];
                    @endphp
                    <span class="badge badge-{{ $colors[$loan->status] ?? 'gray' }}">
                        {{ $labels[$loan->status] ?? ucfirst($loan->status) }}
                    </span>
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="7" class="right">Total:</td>
            <td class="center bold">{{ $loans->count() }}</td>
        </tr>
    </tfoot>
</table>

@endsection
