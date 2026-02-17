<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportTitle }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #059669;
        }
        .header h1 {
            font-size: 16pt;
            color: #059669;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 9pt;
            color: #666;
        }
        .stats {
            background-color: #f3f4f6;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            display: flex;
            justify-content: space-around;
        }
        .stats div {
            text-align: center;
        }
        .stats div strong {
            display: block;
            font-size: 10pt;
            color: #059669;
            margin-bottom: 2px;
        }
        .stats div span {
            font-size: 8pt;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        thead {
            background-color: #059669;
            color: white;
        }
        th {
            padding: 8px 5px;
            text-align: left;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        th.center { text-align: center; }
        th.right { text-align: right; }
        td {
            padding: 6px 5px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 8pt;
        }
        td.center { text-align: center; }
        td.right { text-align: right; }
        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        tbody tr:hover {
            background-color: #f3f4f6;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 7pt;
            color: #999;
        }
        .material-name {
            font-weight: bold;
            color: #111827;
        }
        .material-code {
            font-size: 7pt;
            color: #6b7280;
        }
        .dependency {
            font-weight: 500;
            color: #374151;
        }
        .unit {
            font-size: 7pt;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $reportTitle }}</h1>
        <p>Gerado em {{ now()->format('d/m/Y H:i') }} | Sistema SUBRAVO</p>
    </div>

    <div class="stats">
        <div>
            <strong>{{ number_format($stats['total_items']) }}</strong>
            <span>Total de Itens</span>
        </div>
        <div>
            <strong>{{ number_format($stats['total_quantity']) }}</strong>
            <span>Quantidade Total</span>
        </div>
        <div>
            <strong>R$ {{ number_format($stats['total_value'], 2, ',', '.') }}</strong>
            <span>Valor Total</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Material</th>
                <th style="width: 15%;">Tipo</th>
                <th style="width: 18%;">Localização</th>
                <th class="center" style="width: 5%;">Qtd</th>
                <th class="right" style="width: 12%;">Vlr Unit.</th>
                <th class="right" style="width: 12%;">Vlr Total</th>
                <th class="center" style="width: 8%;">Patrim.</th>
                <th style="width: 5%;">Data</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>
                        <div class="material-name">{{ $item->material_name }}</div>
                        @if($item->material_code || $item->ficha_number)
                            <div class="material-code">
                                @if($item->material_code) Cód: {{ $item->material_code }} @endif
                                @if($item->ficha_number) | Ficha: {{ $item->ficha_number }} @endif
                            </div>
                        @endif
                    </td>
                    <td>{{ $item->material_type ?? '—' }}</td>
                    <td>
                        <div class="dependency">{{ $item->upload->dependency }}</div>
                        <div class="unit">{{ $item->upload->unit }}</div>
                    </td>
                    <td class="center">{{ $item->quantity }}</td>
                    <td class="right">R$ {{ number_format($item->unit_value, 2, ',', '.') }}</td>
                    <td class="right">R$ {{ number_format($item->total_value, 2, ',', '.') }}</td>
                    <td class="center">
                        @if($item->hasPatrimonyNumbers())
                            {{ $item->patrimony_count }}
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $item->created_at->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>SUBRAVO — Sistema de Controle de Estoque e Empréstimos</p>
    </div>
</body>
</html>
