<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Busca de Materiais - SUBRAVO</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            color: #1f2937;
        }
        
        .header {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .header h1 {
            font-size: 18pt;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 9pt;
            opacity: 0.9;
        }
        
        .stats {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        
        .stats .stat-item {
            display: table-cell;
            width: 25%;
            padding: 10px;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        
        .stats .stat-label {
            font-size: 8pt;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        
        .stats .stat-value {
            font-size: 14pt;
            font-weight: bold;
            color: #059669;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        th {
            background-color: #059669;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #047857;
        }
        
        td {
            padding: 6px 5px;
            border: 1px solid #e5e7eb;
            font-size: 8pt;
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
            padding: 2px 6px;
            background: #dbeafe;
            color: #1e40af;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #e5e7eb;
            font-size: 8pt;
            color: #6b7280;
            text-align: center;
        }
        
        .search-info {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 8px;
            margin-bottom: 15px;
            font-size: 9pt;
        }
        
        .search-info strong {
            color: #92400e;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>🔍 Busca de Materiais - SUBRAVO</h1>
        <p>Sistema de Controle de Estoque de Intendência</p>
    </div>

    {{-- Termo de busca --}}
    @if($searchTerm)
        <div class="search-info">
            <strong>Termo de busca:</strong> {{ $searchTerm }}
        </div>
    @endif

    {{-- Estatísticas --}}
    <div class="stats">
        <div class="stat-item">
            <div class="stat-label">Registros</div>
            <div class="stat-value">{{ number_format($stats['total_items']) }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Materiais Distintos</div>
            <div class="stat-value">{{ number_format($stats['unique_materials']) }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Quantidade Total</div>
            <div class="stat-value">{{ number_format($stats['total_quantity']) }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Valor Total</div>
            <div class="stat-value">R$ {{ number_format($stats['total_value'], 2, ',', '.') }}</div>
        </div>
    </div>

    {{-- Tabela de resultados --}}
    @if($items->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th style="width: 25%;">Material</th>
                    <th style="width: 8%;">Código</th>
                    <th style="width: 8%;">Ficha</th>
                    <th style="width: 12%;">Tipo</th>
                    <th style="width: 12%;">Dependência</th>
                    <th style="width: 12%;">Unidade</th>
                    <th style="width: 6%;" class="text-center">Qtd</th>
                    <th style="width: 9%;" class="text-right">Vlr Unit.</th>
                    <th style="width: 9%;" class="text-right">Vlr Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
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
                        <td>{{ $item->upload->dependency ?? '—' }}</td>
                        <td>{{ $item->upload->unit ?? '—' }}</td>
                        <td class="text-center"><strong>{{ $item->quantity }}</strong></td>
                        <td class="text-right">R$ {{ number_format($item->unit_value, 2, ',', '.') }}</td>
                        <td class="text-right"><strong>R$ {{ number_format($item->total_value, 2, ',', '.') }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; padding: 40px; color: #6b7280;">
            Nenhum material encontrado.
        </p>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p>
            <strong>SUBRAVO</strong> — Sistema de Controle de Estoque de Intendência<br>
            Gerado em {{ $generatedAt }} por {{ auth()->user()->war_name }}
        </p>
    </div>
</body>
</html>
