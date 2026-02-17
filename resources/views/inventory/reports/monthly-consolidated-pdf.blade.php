<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consolidado Mensal - {{ $monthName }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8pt;
            color: #1f2937;
        }
        
        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            color: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 20pt;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 10pt;
            opacity: 0.9;
        }
        
        .global-stats {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        
        .global-stats .stat-item {
            display: table-cell;
            width: 20%;
            padding: 10px;
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            text-align: center;
        }
        
        .global-stats .stat-label {
            font-size: 7pt;
            color: #4338ca;
            text-transform: uppercase;
            margin-bottom: 3px;
            font-weight: bold;
        }
        
        .global-stats .stat-value {
            font-size: 14pt;
            font-weight: bold;
            color: #3730a3;
        }
        
        .dependency-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .dependency-header {
            background: #059669;
            color: white;
            padding: 10px;
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 5px;
            border-radius: 3px;
        }
        
        .dependency-stats {
            background: #f0fdf4;
            padding: 8px;
            margin-bottom: 8px;
            border-left: 4px solid #059669;
            font-size: 8pt;
        }
        
        .dependency-stats span {
            margin-right: 15px;
        }
        
        .dependency-stats strong {
            color: #047857;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        th {
            background-color: #047857;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #065f46;
        }
        
        td {
            padding: 4px;
            border: 1px solid #e5e7eb;
            font-size: 7pt;
        }
        
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 5px;
            background: #dbeafe;
            color: #1e40af;
            border-radius: 2px;
            font-size: 6pt;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #e5e7eb;
            font-size: 7pt;
            color: #6b7280;
            text-align: center;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>📅 {{ $reportTitle }}</h1>
        <p>Consolidado de Todas as Dependências — {{ ucfirst($monthName) }}</p>
    </div>

    {{-- Estatísticas Globais --}}
    <div class="global-stats">
        <div class="stat-item">
            <div class="stat-label">Dependências</div>
            <div class="stat-value">{{ $globalStats['total_dependencies'] }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">PDFs Processados</div>
            <div class="stat-value">{{ $globalStats['total_uploads'] }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Total Itens</div>
            <div class="stat-value">{{ number_format($globalStats['total_items']) }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Quantidade Total</div>
            <div class="stat-value">{{ number_format($globalStats['total_quantity']) }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Valor Total</div>
            <div class="stat-value">R$ {{ number_format($globalStats['total_value'], 2, ',', '.') }}</div>
        </div>
    </div>

    {{-- Listagem por Dependência --}}
    @foreach($itemsByDependency as $dependency => $data)
        <div class="dependency-section">
            <div class="dependency-header">
                📁 {{ $dependency ?? 'SEM DEPENDÊNCIA' }}
                @if($data['upload']->unit)
                    / {{ $data['upload']->unit }}
                @endif
            </div>
            
            <div class="dependency-stats">
                <span><strong>Upload:</strong> {{ $data['upload']->filename }}</span>
                <span><strong>Data:</strong> {{ $data['upload']->created_at->format('d/m/Y H:i') }}</span>
                <span><strong>Itens:</strong> {{ number_format($data['stats']['total_items']) }}</span>
                <span><strong>Qtd:</strong> {{ number_format($data['stats']['total_quantity']) }}</span>
                <span><strong>Valor:</strong> R$ {{ number_format($data['stats']['total_value'], 2, ',', '.') }}</span>
            </div>

            @if($data['items']->isNotEmpty())
                <table>
                    <thead>
                        <tr>
                            <th style="width: 35%;">Material</th>
                            <th style="width: 10%;">Código</th>
                            <th style="width: 10%;">Ficha</th>
                            <th style="width: 15%;">Tipo</th>
                            <th style="width: 8%;" class="text-center">Qtd</th>
                            <th style="width: 11%;" class="text-right">Vlr Unit.</th>
                            <th style="width: 11%;" class="text-right">Vlr Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['items']->sortBy('material_name') as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->material_name }}</strong>
                                    @if($item->hasPatrimonyNumbers())
                                        <br><span class="badge">{{ $item->patrimony_count }} patr.</span>
                                    @endif
                                </td>
                                <td>{{ $item->material_code ?? '—' }}</td>
                                <td>{{ $item->ficha_number ?? '—' }}</td>
                                <td>{{ $item->material_type ?? '—' }}</td>
                                <td class="text-center"><strong>{{ $item->quantity }}</strong></td>
                                <td class="text-right">R$ {{ number_format($item->unit_value, 2, ',', '.') }}</td>
                                <td class="text-right"><strong>R$ {{ number_format($item->total_value, 2, ',', '.') }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Page break entre dependências (exceto última) --}}
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    {{-- Footer --}}
    <div class="footer">
        <p>
            <strong>SUBRAVO</strong> — Sistema de Controle de Estoque de Intendência<br>
            Relatório Consolidado Mensal — {{ ucfirst($monthName) }}<br>
            Gerado em {{ now()->format('d/m/Y H:i:s') }} por {{ auth()->user()->war_name }}
        </p>
    </div>
</body>
</html>
