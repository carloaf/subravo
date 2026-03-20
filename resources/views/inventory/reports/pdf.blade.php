@extends('layouts.pdf-inventory')

@section('title', $reportTitle . ' - HelpSub')
@section('location', 'DE INVENTÁRIO GERAL')

@php
    $period = now()->format('d/m/Y');
@endphp

@section('content')

{{-- Cards de Resumo --}}
<table class="summary">
    <tr>
        <td>
            <div class="label">Total de Itens</div>
            <div class="value">{{ number_format($stats['total_items'], 0, ',', '.') }}</div>
        </td>
        <td>
            <div class="label">Quantidade Total</div>
            <div class="value">{{ number_format($stats['total_quantity'], 0, ',', '.') }}</div>
        </td>
        <td>
            <div class="label">Valor Total</div>
            <div class="value">R$ {{ number_format($stats['total_value'], 2, ',', '.') }}</div>
        </td>
    </tr>
</table>

<div class="section-title">
    INVENTÁRIO — DETALHAMENTO COMPLETO DE ITENS
</div>

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 25%;">Material</th>
            <th style="width: 12%;">Tipo</th>
            <th style="width: 18%;">Localização</th>
            <th class="center" style="width: 7%;">Qtd</th>
            <th class="right" style="width: 11%;">Vlr Unit.</th>
            <th class="right" style="width: 11%;">Vlr Total</th>
            <th class="center" style="width: 8%;">Patrim.</th>
            <th class="center" style="width: 8%;">Data</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td class="left">
                    <strong>{{ $item->material_name }}</strong>
                    @if($item->material_code || $item->ficha_number)
                        <br><span style="font-size: 7px; color: #6b7280;">
                            @if($item->material_code) Cód: {{ $item->material_code }} @endif
                            @if($item->ficha_number) | Ficha: {{ $item->ficha_number }} @endif
                        </span>
                    @endif
                </td>
                <td class="center" style="font-size: 8px;">
                    @if($item->material_type)
                        @php
                            $typeText = str_ireplace('Material Permanente', 'Perman', $item->material_type);
                            $typeText = Str::limit($typeText, 12);
                        @endphp
                        <span class="badge badge-green">{{ $typeText }}</span>
                    @else
                        <span style="color: #9ca3af;">—</span>
                    @endif
                </td>
                <td class="left" style="font-size: 8px;">
                    <strong style="color: #374151;">{{ $item->upload->dependency }}</strong>
                    @if($item->upload->unit)
                        <br><span style="color: #6b7280;">{{ $item->upload->unit }}</span>
                    @endif
                </td>
                <td class="center bold text-blue">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                <td class="right" style="font-size: 8px;">R$ {{ number_format($item->unit_value, 2, ',', '.') }}</td>
                <td class="right bold">R$ {{ number_format($item->total_value, 2, ',', '.') }}</td>
                <td class="center">
                    @if($item->hasPatrimonyNumbers())
                        <span class="badge badge-blue" style="font-size: 7px;">{{ $item->patrimony_count }}</span>
                    @else
                        <span style="color: #9ca3af;">—</span>
                    @endif
                </td>
                <td class="center" style="font-size: 7px; color: #6b7280;">{{ $item->created_at->format('d/m/Y') }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" class="right" style="font-size: 10px;">TOTAIS GERAIS:</td>
            <td class="center bold text-blue" style="font-size: 10px;">{{ number_format($stats['total_quantity'], 0, ',', '.') }}</td>
            <td colspan="1"></td>
            <td class="right bold" style="font-size: 11px;">R$ {{ number_format($stats['total_value'], 2, ',', '.') }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>

@endsection
