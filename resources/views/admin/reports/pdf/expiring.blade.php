@extends('layouts.pdf')

@section('title', $reportTitle)
@section('report-title', $reportTitle)
@section('meta-right', $items->count() . ' itens próximos da validade (60 dias)')

@section('content')

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 5%">#</th>
            <th style="width: 25%">Produto</th>
            <th style="width: 15%">Categoria</th>
            <th style="width: 10%">Lote</th>
            <th style="width: 8%">Qtd</th>
            <th style="width: 12%">Validade</th>
            <th style="width: 10%">Dias Rest.</th>
            <th style="width: 15%">Localização</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $i => $item)
            @php
                $daysLeft = (int) now()->diffInDays($item->expiration_date, false);
                $isExpired = $daysLeft < 0;
            @endphp
            <tr class="{{ $isExpired ? 'bg-red-light' : ($daysLeft <= 15 ? 'bg-amber-light' : '') }}">
                <td class="center">{{ $i + 1 }}</td>
                <td class="left">{{ $item->product?->name ?? '—' }}</td>
                <td class="left">{{ $item->product?->category?->name ?? '—' }}</td>
                <td class="center">{{ $item->batch ?? '—' }}</td>
                <td class="center">{{ $item->quantity }}</td>
                <td class="center bold {{ $isExpired ? 'text-red' : 'text-amber' }}">
                    {{ $item->expiration_date->format('d/m/Y') }}
                </td>
                <td class="center">
                    @if($isExpired)
                        <span class="badge badge-red">VENCIDO</span>
                    @elseif($daysLeft <= 15)
                        <span class="badge badge-amber">{{ $daysLeft }}d</span>
                    @else
                        {{ $daysLeft }}d
                    @endif
                </td>
                <td class="left">{{ $item->location ?? '—' }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="right">Total de Itens:</td>
            <td class="center bold">{{ $items->sum('quantity') }}</td>
            <td colspan="3"></td>
        </tr>
    </tfoot>
</table>

@if($items->isEmpty())
    <p style="text-align: center; padding: 20px; color: #16a34a; font-weight: bold; font-size: 12px;">
        ✓ Nenhum item com validade próxima. Estoque dentro da validade.
    </p>
@endif

@endsection
