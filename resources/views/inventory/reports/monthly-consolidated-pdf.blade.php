@extends('layouts.pdf')

@section('title', 'Consolidado Mensal — ' . ucfirst($monthName) . ' — SUBRAVO')
@section('report-title', '📅 ' . $reportTitle . ' — ' . ucfirst($monthName))
@section('meta-user', auth()->user()->war_name)
@section('meta-date', now()->format('d/m/Y H:i'))
@section('meta-right')
    <strong>Período:</strong> {{ ucfirst($monthName) }}
@endsection

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
        <td style="background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); border-left: 4px solid #6366f1;">
            <div class="label">🏢 Dependências</div>
            <div class="value text-indigo">{{ number_format($globalStats['total_dependencies'], 0, ',', '.') }}</div>
        </td>
        <td style="background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); border-left: 4px solid #4f46e5;">
            <div class="label">📄 PDFs Processados</div>
            <div class="value text-indigo">{{ number_format($globalStats['total_uploads'], 0, ',', '.') }}</div>
        </td>
        <td style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border-left: 4px solid #3b82f6;">
            <div class="label">📋 Total de Itens</div>
            <div class="value text-blue">{{ number_format($globalStats['total_items'], 0, ',', '.') }}</div>
        </td>
        <td style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border-left: 4px solid #10b981;">
            <div class="label">📦 Quantidade Total</div>
            <div class="value text-green">{{ number_format($globalStats['total_quantity'], 0, ',', '.') }}</div>
        </td>
        <td style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-left: 4px solid #f59e0b;">
            <div class="label">💰 Valor Total</div>
            <div class="value text-amber">R$ {{ number_format($globalStats['total_value'], 2, ',', '.') }}</div>
        </td>
    </tr>
</table>

<div class="alert alert-info" style="margin-bottom: 20px;">
    <strong>ℹ️ RELATÓRIO CONSOLIDADO:</strong> Este documento apresenta o consolidado de todas as dependências para o período de 
    <strong>{{ ucfirst($monthName) }}</strong>, abrangendo <strong>{{ $globalStats['total_dependencies'] }}</strong> dependência(s) 
    e <strong>{{ $globalStats['total_uploads'] }}</strong> PDF(s) processado(s).
</div>

{{-- Listagem por Dependência --}}
@foreach($itemsByDependency as $dependency => $data)
    <div class="dependency-section">
        
        <div class="section-title" style="border-left-color: #059669; background: linear-gradient(90deg, #ecfdf5 0%, #d1fae5 50%, #ecfdf5 100%); color: #065f46; border-top: 2px solid #10b981; border-bottom: 2px solid #10b981;">
            📁 {{ strtoupper($dependency ?? 'SEM DEPENDÊNCIA') }}
            @if($data['upload']->unit)
                / {{ strtoupper($data['upload']->unit) }}
            @endif
        </div>
        
        {{-- Info da Dependência --}}
        <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-left: 4px solid #059669; padding: 10px; margin-bottom: 12px; border-radius: 4px; font-size: 8px;">
            <table style="width: 100%; border: none; margin: 0;">
                <tr>
                    <td style="border: none; padding: 3px; width: 40%;"><strong style="color: #065f46;">📁 Arquivo:</strong> <span style="color: #047857;">{{ $data['upload']->filename }}</span></td>
                    <td style="border: none; padding: 3px; width: 20%;"><strong style="color: #065f46;">📅 Data:</strong> <span style="color: #047857;">{{ $data['upload']->created_at->format('d/m/Y H:i') }}</span></td>
                    <td style="border: none; padding: 3px; width: 13%;"><strong style="color: #065f46;">📋 Itens:</strong> <span class="badge badge-blue" style="font-size: 7px;">{{ number_format($data['stats']['total_items']) }}</span></td>
                    <td style="border: none; padding: 3px; width: 13%;"><strong style="color: #065f46;">📦 Qtd:</strong> <span class="badge badge-green" style="font-size: 7px;">{{ number_format($data['stats']['total_quantity']) }}</span></td>
                    <td style="border: none; padding: 3px; width: 14%;"><strong style="color: #065f46;">💰 Valor:</strong> <span class="badge badge-amber" style="font-size: 7px;">R$ {{ number_format($data['stats']['total_value'], 2, ',', '.') }}</span></td>
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
                                    <br><span class="badge badge-blue" style="font-size: 6px; margin-top: 2px;">📍 {{ $item->patrimony_count }} patr.</span>
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
                            <td class="right bold" style="color: #065f46; font-size: 8px;">R$ {{ number_format($item->total_value, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-info">
                <strong>ℹ️ Sem Itens:</strong> Nenhum material cadastrado para esta dependência.
            </div>
        @endif
    </div>

    {{-- Page break entre dependências (exceto última) --}}
    @if(!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach

@endsection
