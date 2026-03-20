<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Relatorio de Inventario - HelpSub')</title>
    <style>
        @page { 
            margin: 25mm 0mm 20mm 0mm; 
            size: A4 landscape;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            font-size: 9px; 
            color: #000; 
            line-height: 1.4; 
        }
        
        .page {
            padding: 0 10mm;
        }

        /* Cabeçalho Simplificado */
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #000;
        }
        .header h1 {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 12px;
        }
        .header-meta {
            font-size: 9px;
            line-height: 1.6;
        }
        .header-meta strong {
            font-weight: bold;
        }

        /* Título de Seção */
        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 10px;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            margin: 15px 0 10px 0;
        }
        .section-title.material {
            background: #d1fae5;
            border: 1px solid #10b981;
            color: #065f46;
        }

        /* Tabelas */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th {
            background: #e5e7eb;
            border: 1px solid #9ca3af;
            padding: 6px 4px;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }
        td {
            border: 1px solid #d1d5db;
            padding: 5px 4px;
            font-size: 8px;
        }
        tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        .center { text-align: center; }
        .left { text-align: left; }
        .right { text-align: right; }
        .bold { font-weight: bold; }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }
        .badge-blue { background: #dbeafe; color: #1e40af; border: 1px solid #3b82f6; }
        .badge-purple { background: #e9d5ff; color: #6b21a8; border: 1px solid #9333ea; }
        .badge-green { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .text-blue { color: #1e40af; }
        .text-green { color: #065f46; }

        /* Resumo */
        .summary {
            margin-bottom: 15px;
        }
        .summary td {
            padding: 8px;
            text-align: center;
            border: 1px solid #d1d5db;
        }
        .summary .label {
            font-size: 8px;
            color: #6b7280;
            margin-bottom: 3px;
        }
        .summary .value {
            font-size: 13px;
            font-weight: bold;
        }

        /* Alertas */
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid;
            border-radius: 4px;
            font-size: 8px;
        }
        .alert-info {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #1e40af;
        }

        /* Rodapé */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #d1d5db;
            font-size: 7px;
            text-align: center;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="page">
        {{-- Cabeçalho Simplificado --}}
        <div class="header">
            <h1>11º Depósito de Suprimento — Relatório de Material</h1>
            <h2>RELATÓRIO @yield('location', 'DE INVENTÁRIO GERAL')</h2>
            <div class="header-meta">
                <strong>DATA DE GERAÇÃO:</strong> {{ date('d/m/Y H:i') }}<br>
                <strong>GERADO POR:</strong> {{ strtoupper(auth()->user()->war_name) }}<br>
                @if(isset($period))
                    <strong>PERÍODO:</strong> {{ $period }}
                @elseif(isset($dateRange))
                    <strong>PERÍODO:</strong> {{ $dateRange }}
                @else
                    <strong>PERÍODO:</strong> {{ date('d/m/Y') }}
                @endif
            </div>
        </div>

        {{-- Conteúdo Específico --}}
        @yield('content')

        {{-- Rodapé --}}
        <div class="footer">
            Sistema HelpSub - 11o Deposito de Suprimento | Gerado em {{ date('d/m/Y \a\s H:i:s') }} | Documento Oficial
        </div>
    </div>
</body>
</html>
