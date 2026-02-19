@extends('layouts.pdf-inventory')

@section('title', 'Consolidado Mensal — ' . ucfirst($monthName) . ' — SUBRAVO')
@section('location', 'CONSOLIDADO MENSAL - ' . strtoupper($monthName))

@php
    $period = ucfirst($monthName);
@endphp

@section('content')

<style>
    .page-break {
        page-break-before: always;
    }
    .dependency-section {
        margin-bottom: 20px;
        page-break-inside: avoid;
    }
</style>

{{-- Estatísticas Globais do Mês --}}
<table class="summary">
    <tr>
        <td>
            <div class="label">Dependências</div>
            <div class="value">{{ number_format($globalStats['total_dependencies'], 0, ',', '.') }}</div>
        </td>
        <td>
            <div class="label">PDFs Processados</div>
            <div class="value">{{ number_format($globalStats['total_uploads'], 0, ',', '.') }}</div>
        </td>
        <td>
            <div class="label">Total de Itens</div>
            <div class="value">{{ number_format($globalStats['total_items'], 0, ',', '.') }}</div>
        </td>
        <td>
            <div class="label">Quantidade Total</div>
            <div class="value">{{ number_format($globalStats['total_quantity'], 0, ',', '.') }}</div>
        </td>
        <td>
            <div class="label">Valor Total</div>
            <div class="value">R$ {{ number_format($globalStats['total_value'], 2, ',', '.') }}</div>
        </td>
    </tr>
</table>

<div class="alert alert-info" style="margin-bottom: 20px;">
    <strong>RELATÓRIO CONSOLIDADO:</strong> Este documento apresenta o consolidado de todas as dependências para o período de 
    <strong>{{ ucfirst($monthName) }}</strong>, abrangendo <strong>{{ $globalStats['total_dependencies'] }}</strong> dependência(s) 
    e <strong>{{ $globalStats['total_uploads'] }}</strong> PDF(s) processado(s).
</div>

{{-- Listagem por Dependência --}}
@foreach($itemsByDependency as $dependency => $data)
    <div class="dependency-section">
        
        <div class="section-title material">
            {{ strtoupper($dependency ?? 'SEM DEPENDÊNCIA') }}
            @if($data['upload']->unit)
                / {{ strtoupper($data['upload']->unit) }}
            @endif
        </div>
        
        {{-- Info da Dependência --}}
        <div style="background: #f3f4f6; border-left: 4px solid #6b7280; padding: 10px; margin-bottom: 12px; border-radius: 4px; font-size: 8px;">
            <table style="width: 100%; border: none; margin: 0;">
                <tr>
                    <td style="border: none; padding: 3px; width: 40%;"><strong>Arquivo:</strong> <span>{{ $data['upload']->filename }}</span></td>
                    <td style="border: none; padding: 3px; width: 20%;"><strong>Data:</strong> <span>{{ $data['upload']->created_at->format('d/m/Y H:i') }}</span></td>
                    <td style="border: none; padding: 3px; width: 13%;"><strong>Itens:</strong> <span class="badge badge-blue" style="font-size: 7px;">{{ number_format($data['stats']['total_items']) }}</span></td>
                    <td style="border: none; padding: 3px; width: 13%;"><strong>Qtd:</strong> <span class="badge badge-green" style="font-size: 7px;">{{ number_format($data['stats']['total_quantity']) }}</span></td>
                    <td style="border: none; padding: 3px; width: 14%;"><strong>Valor:</strong> <span class="badge" style="font-size: 7px; background: #fef3c7; color: #92400e; border: 1px solid #f59e0b;">R$ {{ number_format($data['stats']['total_value'], 2, ',', '.') }}</span></td>
                </tr>
            </table>
        </div>

        @if($data['items']->isNotEmpty())
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 35%;">Material</th>
                        <th style="width: 10%;">Código</th>
                        <th style="width: 9%;">Ficha</th>
                        <th style="width: 13%;">Tipo</th>
                        <th class="center" style="width: 8%;">Qtd</th>
                        <th class="right" style="width: 12%;">Vlr Unit.</th>
                        <th class="right" style="width: 13%;">Vlr Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['items']->sortBy('material_name') as $item)
                        <tr>
                            <td class="left">
                                <strong>{{ $item->material_name }}</strong>
                                @if($item->hasPatrimonyNumbers())
                                    <br><span class="badge badge-blue" style="font-size: 6px; margin-top: 2px;">{{ $item->patrimony_count }} patr.</span>
                                @endif
                            </td>
                            <td class="center" style="font-family: 'Courier New'; font-size: 7px;">{{ $item->material_code ?? '—' }}</td>
                            <td class="center" style="font-family: 'Courier New'; font-size: 7px;">{{ $item->ficha_number ?? '—' }}</td>
                            <td class="center" style="font-size: 7px;">
                                @if($item->material_type)
                                    <span class="badge badge-purple">{{ Str::limit($item->material_type, 12) }}</span>
                                @else
                                    <span style="color: #9ca3af;">—</span>
                                @endif
                            </td>
                            <td class="center bold text-blue" style="font-size: 9px;">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                            <td class="right" style="font-size: 7px;">R$ {{ number_format($item->unit_value, 2, ',', '.') }}</td>
                            <td class="right bold" style="font-size: 8px;">R$ {{ number_format($item->total_value, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-info">
                <strong>Sem Itens:</strong> Nenhum material cadastrado para esta dependência.
            </div>
        @endif
    </div>

    {{-- Page break entre dependências (exceto última) --}}
    @if(!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach

@endsection
