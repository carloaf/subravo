@extends('layouts.pdf')

@section('title', $reportTitle)
@section('report-title', $reportTitle)
@section('meta-right', $loans->count() . ' cautelas ativas')

@section('content')

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 5%">#</th>
            <th style="width: 15%">Nº Cautela</th>
            <th style="width: 22%">Mutuário</th>
            <th style="width: 10%">OM</th>
            <th style="width: 10%">Data</th>
            <th style="width: 10%">Prev. Devol.</th>
            <th style="width: 8%">Itens</th>
            <th style="width: 10%">Expedido por</th>
            <th style="width: 10%">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($loans as $i => $loan)
            @php $overdue = $loan->expected_return_date && $loan->expected_return_date->isPast(); @endphp
            <tr class="{{ $overdue ? 'bg-red-light' : '' }}">
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
                <td class="center {{ $overdue ? 'text-red bold' : '' }}">
                    {{ $loan->expected_return_date?->format('d/m/Y') ?? '—' }}
                </td>
                <td class="center">{{ $loan->items->count() }}</td>
                <td class="center">{{ $loan->loanedBy?->war_name ?? '—' }}</td>
                <td class="center">
                    @if($overdue)
                        <span class="badge badge-red">ATRASADA</span>
                    @else
                        <span class="badge badge-amber">ATIVA</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" class="right">Total de Cautelas:</td>
            <td class="center bold">{{ $loans->sum(fn($l) => $l->items->count()) }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>

@endsection
