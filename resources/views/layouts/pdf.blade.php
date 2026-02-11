<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Relatório SUBRAVO')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #1a1a1a; line-height: 1.4; }
        .page { padding: 15px 20px; }

        /* Header */
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 8px; margin-bottom: 10px; }
        .header h1 { font-size: 13px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px; }
        .header h2 { font-size: 11px; font-weight: normal; color: #555; }
        .header .subtitle { font-size: 9px; color: #777; margin-top: 3px; }

        /* Report Title */
        .report-title { text-align: center; font-size: 14px; font-weight: bold; margin: 10px 0; padding: 5px; background: #f0f0f0; border: 1px solid #ccc; }

        /* Meta info */
        .meta { display: table; width: 100%; margin-bottom: 10px; font-size: 9px; color: #666; }
        .meta-left { display: table-cell; text-align: left; }
        .meta-right { display: table-cell; text-align: right; }

        /* Tables */
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .data-table th { background: #e8e8e8; border: 1px solid #999; padding: 4px 5px; font-size: 8px; text-transform: uppercase; text-align: center; font-weight: bold; }
        .data-table td { border: 1px solid #bbb; padding: 3px 5px; font-size: 9px; }
        .data-table td.left { text-align: left; }
        .data-table td.center { text-align: center; }
        .data-table td.right { text-align: right; }
        .data-table td.bold { font-weight: bold; }
        .data-table tr:nth-child(even) { background: #fafafa; }
        .data-table tfoot td { font-weight: bold; background: #f0f0f0; border-top: 2px solid #999; }

        /* Status badges */
        .badge { display: inline-block; padding: 1px 6px; border-radius: 8px; font-size: 8px; font-weight: bold; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-amber { background: #fef3c7; color: #92400e; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-purple { background: #f3e8ff; color: #6b21a8; }
        .badge-gray { background: #f3f4f6; color: #374151; }

        /* Summary boxes */
        .summary { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .summary td { padding: 6px 10px; border: 1px solid #ddd; text-align: center; }
        .summary .label { font-size: 8px; text-transform: uppercase; color: #666; }
        .summary .value { font-size: 16px; font-weight: bold; color: #333; }

        /* Highlight */
        .text-green { color: #16a34a; }
        .text-red { color: #dc2626; }
        .text-amber { color: #d97706; }
        .text-blue { color: #2563eb; }
        .bg-red-light { background: #fff5f5; }
        .bg-amber-light { background: #fffbeb; }

        /* Footer */
        .footer { margin-top: 15px; text-align: center; font-size: 7px; color: #999; border-top: 1px solid #ddd; padding-top: 4px; }

        /* Section */
        .section-title { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #333; border-bottom: 1px solid #999; padding-bottom: 2px; margin: 10px 0 6px; }
    </style>
    @yield('styles')
</head>
<body>
<div class="page">

    <div class="header">
        <h1>MINISTÉRIO DA DEFESA</h1>
        <h2>Sistema SUBRAVO — Controle de Material de Intendência</h2>
        <div class="subtitle">Relatório gerado automaticamente</div>
    </div>

    <div class="report-title">
        @yield('report-title', 'Relatório')
    </div>

    <div class="meta">
        <span class="meta-left">
            Gerado em: {{ $generatedAt ?? now()->format('d/m/Y H:i') }}
            &nbsp;|&nbsp; Por: {{ $generatedBy ?? '—' }}
        </span>
        <span class="meta-right">
            @yield('meta-right')
        </span>
    </div>

    @yield('content')

    <div class="footer">
        Documento gerado pelo Sistema SUBRAVO em {{ now()->format('d/m/Y \à\s H:i') }}
        &mdash; Relatório para uso interno da Organização Militar.
    </div>

</div>
</body>
</html>
