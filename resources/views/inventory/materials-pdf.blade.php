@extends('layouts.pdf')

@section('title', 'Busca de Materiais — SUBRAVO')
@section('report-title', '🔍 Busca de Materiais — Inventário SISCOFIS')
@section('meta-user', auth()->user()->war_name)
@section('meta-date', $generatedAt)
@section('meta-right')
    @if($searchTerm)
        <strong>Termo de Busca:</strong> "{{ $searchTerm }}"
    @else
        <strong>Filtro:</strong> Todos os materiais
    @endif
@endsection

@section('content')

{{-- Cards de Resumo --}}
<table class="summary">
    <tr>
        <td style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border-left: 4px solid #3b82f6;">
            <div class="label">📋 Registros</div>
            <div class="value text-blue">{{ number_format($stats['total_items'], 0, ',', '.') }}</div>
        </td>
        <td style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border-left: 4px solid #2563eb;">
            <div class="label">🎯 Materiais Distintos</div>
            <div class="value text-blue">{{ number_format($stats['unique_materials'], 0, ',', '.') }}</div>
        </td>
        <td style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border-left: 4px solid #10b981;">
            <div class="label">📦 Quantidade Total</div>
            <div class="value text-green">{{ number_format($stats['total_quantity'], 0, ',', '.') }}</div>
        </td>
        <td style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-left: 4px solid #f59e0b;">
            <div class="label">💰 Valor Total</div>
            <div class="value text-amber">R$ {{ number_format($stats['total_value'], 2, ',', '.') }}</div>
        </td>
    </tr>
</table>

@if($searchTerm)
<div class="alert alert-info">
    <strong>ℹ️ CRITÉRIO DE BUSCA:</strong> Resultados filtrados pelo termo "<strong>{{ $searchTerm }}</strong>". 
    Busca realizada em {{ number_format($stats['total_items']) }} registro(s) encontrado(s) em todos os inventários carregados no sistema.
</div>
@endif

<div class="section-title" style="border-left-color: #3b82f6; background: linear-gradient(90deg, #eff6ff 0%, #dbeafe 50%, #eff6ff 100%); color: #1e40af; border-top: 2px solid #60a5fa; border-bottom: 2px solid #60a5fa;">
    📊 Resultados da Busca — Detalhamento Completo
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
                        <br><span class="badge badge-blue" style="font-size: 7px; margin-top: 3px;">📍 {{ $item->patrimony_count }} patrimônio(s)</span>
                    @endif
                </td>
                <td class="center" style="font-family: 'Courier New'; font-size: 8px;">{{ $item->material_code ?? '—' }}</td>
                <td class="center" style="font-family: 'Courier New'; font-size: 8px;">{{ $item->ficha_number ?? '—' }}</td>
                <td class="center" style="font-size: 8px;">
                    @if($item->material_type)
                        <span class="badge badge-purple">{{ Str::limit($item->material_type, 15) }}</span>
                    @else
                        <span style="color: #9ca3af;">—</span>
                    @endif
                </td>
                <td class="left" style="font-size: 8px; color: #6b7280;">{{ $item->upload->dependency ?? '—' }}</td>
                <td class="left" style="font-size: 8px; color: #6b7280;">{{ $item->upload->unit ?? '—' }}</td>
                <td class="center bold text-blue">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                <td class="right" style="font-size: 8px;">R$ {{ number_format($item->unit_value, 2, ',', '.') }}</td>
                <td class="right bold" style="color: #065f46; font-size: 9px;">R$ {{ number_format($item->total_value, 2, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="7" class="right" style="font-size: 10px;">TOTAIS GERAIS:</td>
            <td class="center bold text-blue" style="font-size: 10px;">{{ number_format($stats['total_quantity'], 0, ',', '.') }}</td>
            <td colspan="1"></td>
            <td class="right bold" style="color: #065f46; font-size: 11px;">R$ {{ number_format($stats['total_value'], 2, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>
@else
<div class="alert alert-info" style="margin-top: 20px;">
    <strong>ℹ️ Nenhum Material Encontrado:</strong> Não foram encontrados materiais com os critérios de busca especificados. 
    Tente refinar sua busca ou verificar se há inventários cadastrados no sistema.
</div>
@endif

@endsection
