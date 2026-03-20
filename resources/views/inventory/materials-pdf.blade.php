@extends('layouts.pdf-inventory')

@section('title', 'Busca de Materiais - HelpSub')
@section('location', 'DE BUSCA DE MATERIAIS')

@php
    $period = $searchTerm ? 'Busca: "' . $searchTerm . '"' : 'Todos os materiais';
@endphp

@section('content')

{{-- Cards de Resumo --}}
<table class="summary">
    <tr>
        <td>
            <div class="label">Registros</div>
            <div class="value">{{ number_format($stats['total_items'], 0, ',', '.') }}</div>
        </td>
        <td>
            <div class="label">Materiais Distintos</div>
            <div class="value">{{ number_format($stats['unique_materials'], 0, ',', '.') }}</div>
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

@if($searchTerm)
<div class="alert alert-info">
    <strong>CRITÉRIO DE BUSCA:</strong> Resultados filtrados pelo termo "<strong>{{ $searchTerm }}</strong>". 
    Busca realizada em {{ number_format($stats['total_items']) }} registro(s) encontrado(s) em todos os inventários carregados no sistema.
</div>
@endif

<div class="section-title">
    INVENTÁRIO — DETALHAMENTO COMPLETO DE ITENS
</div>

@if($items->isNotEmpty())
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%;">#</th>
            <th style="width: 24%;">Material</th>
            <th style="width: 8%;">Cód. Mat</th>
            <th style="width: 7%;">Nr Ficha</th>
            <th style="width: 11%;">Tipo</th>
            <th style="width: 13%;">Dependência</th>
            <th style="width: 10%;">Unidade</th>
            <th style="width: 6%;">Qtd.</th>
            <th style="width: 8%;">Vlr Unit.</th>
            <th style="width: 9%;">Vlr Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $i => $item)
            <tr>
                <td class="center" style="color: #9ca3af;">{{ $i + 1 }}</td>
                <td class="left">
                    <strong>{{ $item->material_name }}</strong>
                    @if($item->hasPatrimonyNumbers())
                        <br><span class="badge badge-blue" style="font-size: 7px; margin-top: 3px;">{{ $item->patrimony_count }} patrimônio(s)</span>
                    @endif
                </td>
                <td class="center" style="font-family: 'Courier New'; font-size: 8px;">{{ $item->material_code ?? '—' }}</td>
                <td class="center" style="font-family: 'Courier New'; font-size: 8px;">{{ $item->ficha_number ?? '—' }}</td>
                <td class="center" style="font-size: 8px;">
                    @if($item->material_type)
                        @php
                            $typeText = str_ireplace('Material Permanente', 'Perman', $item->material_type);
                            $typeText = Str::limit($typeText, 15);
                        @endphp
                        <span class="badge badge-green">{{ $typeText }}</span>
                    @else
                        <span style="color: #9ca3af;">—</span>
                    @endif
                </td>
                <td class="left" style="font-size: 8px; color: #6b7280;">{{ $item->upload->dependency ?? '—' }}</td>
                <td class="left" style="font-size: 8px; color: #6b7280;">{{ $item->upload->unit ?? '—' }}</td>
                <td class="center bold text-blue">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                <td class="right" style="font-size: 8px;">R$ {{ number_format($item->unit_value, 2, ',', '.') }}</td>
                <td class="right bold" style="font-size: 9px;">R$ {{ number_format($item->total_value, 2, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="7" class="right" style="font-size: 10px;">TOTAIS GERAIS:</td>
            <td class="center bold text-blue" style="font-size: 10px;">{{ number_format($stats['total_quantity'], 0, ',', '.') }}</td>
            <td colspan="1"></td>
            <td class="right bold" style="font-size: 11px;">R$ {{ number_format($stats['total_value'], 2, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>
@else
<div class="alert alert-info" style="margin-top: 20px;">
    <strong>Nenhum Material Encontrado:</strong> Não foram encontrados materiais com os critérios de busca especificados. 
    Tente refinar sua busca ou verificar se há inventários cadastrados no sistema.
</div>
@endif

@endsection
