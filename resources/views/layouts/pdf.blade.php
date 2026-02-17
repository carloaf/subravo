<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Relatório SUBRAVO')</title>
    <style>
        @page { 
            margin: 25mm 30mm 25mm 30mm; 
            size: A4;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            font-size: 10px; 
            color: #111827; 
            line-height: 1.6; 
            background: white;
        }
        .page { 
            padding: 0;
            position: relative;
        }

        /* ========================================
           CABEÇALHO INSTITUCIONAL PROFISSIONAL
           ======================================== */
        .header { 
            background: linear-gradient(135deg, #065f46 0%, #047857 50%, #059669 100%);
            color: white;
            padding: 0;
            margin-bottom: 25px;
            border-radius: 0;
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
        }
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255,255,255,0.03) 10px,
                rgba(255,255,255,0.03) 20px
            );
            pointer-events: none;
        }
        .header-top {
            background: rgba(0,0,0,0.2);
            padding: 12px 25px;
            border-bottom: 2px solid rgba(255,255,255,0.2);
        }
        .header-top-content {
            display: table;
            width: 100%;
        }
        .header-brasao {
            display: table-cell;
            width: 70px;
            vertical-align: middle;
            text-align: center;
        }
        .brasao-container {
            width: 55px;
            height: 55px;
            background: white;
            border-radius: 50%;
            display: inline-block;
            padding: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .brasao-text {
            font-size: 24px;
            line-height: 39px;
            color: #065f46;
            font-weight: bold;
        }
        .header-institucional {
            display: table-cell;
            vertical-align: middle;
            padding-left: 15px;
        }
        .header-institucional h1 { 
            font-size: 13px; 
            font-weight: 700;
            text-transform: uppercase; 
            letter-spacing: 2px; 
            margin-bottom: 3px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .header-institucional h2 { 
            font-size: 11px; 
            font-weight: 500; 
            opacity: 0.95;
            letter-spacing: 0.5px;
        }
        .header-institucional .subtitle { 
            font-size: 9px; 
            opacity: 0.85;
            margin-top: 3px;
            font-style: italic;
        }
        .header-sistema {
            display: table-cell;
            width: 140px;
            text-align: right;
            vertical-align: middle;
        }
        .sistema-badge {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #78350f;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 3px;
            display: inline-block;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            border: 2px solid rgba(255,255,255,0.3);
        }
        .header-main {
            padding: 18px 25px;
            background: rgba(255,255,255,0.08);
        }

        /* Report Title com Destaque */
        .report-title { 
            background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
            text-align: center; 
            font-size: 16px; 
            font-weight: 800; 
            margin: 0 0 20px 0;
            padding: 20px 30px; 
            border-left: 6px solid #047857;
            border-right: 6px solid #047857;
            border-top: 3px solid #e5e7eb;
            border-bottom: 3px solid #e5e7eb;
            color: #065f46;
            text-transform: uppercase;
            letter-spacing: 2px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.08);
            position: relative;
        }
        .report-title::before {
            content: '📊';
            position: absolute;
            left: 30px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 28px;
            opacity: 0.3;
        }
        .report-title::after {
            content: '📊';
            position: absolute;
            right: 30px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 28px;
            opacity: 0.3;
        }

        /* Meta Info Redesenhada */
        .meta { 
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            display: table; 
            width: 100%; 
            padding: 16px 25px; 
            font-size: 9px; 
            color: #065f46;
            border: 2px solid #047857;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }
        .meta-left { 
            display: table-cell; 
            text-align: left; 
            width: 50%;
            vertical-align: top;
            line-height: 1.8;
        }
        .meta-right { 
            display: table-cell; 
            text-align: right; 
            width: 50%;
            vertical-align: top;
            line-height: 1.8;
        }
        .meta strong {
            color: #065f46;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.5px;
        }

        /* Tables Profissionais */
        .data-table { 
            width: 100%; 
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 25px; 
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            border: 2px solid #e5e7eb;
        }
        .data-table th { 
            background: linear-gradient(180deg, #065f46 0%, #047857 50%, #059669 100%);
            color: white;
            border: none;
            padding: 12px 10px; 
            font-size: 9px; 
            text-transform: uppercase; 
            text-align: center; 
            font-weight: 700;
            letter-spacing: 0.8px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }
        .data-table th:first-child {
            border-radius: 8px 0 0 0;
        }
        .data-table th:last-child {
            border-radius: 0 8px 0 0;
        }
        .data-table td { 
            border-bottom: 1px solid #e5e7eb;
            border-right: 1px solid #f3f4f6;
            padding: 10px 8px; 
            font-size: 9px; 
            background: white;
        }
        .data-table td:last-child {
            border-right: none;
        }
        .data-table tr:last-child td {
            border-bottom: none;
        }
        .data-table td.left { text-align: left; }
        .data-table td.center { text-align: center; }
        .data-table td.right { text-align: right; }
        .data-table td.bold { font-weight: 700; color: #111827; }
        .data-table tbody tr:nth-child(odd) { background: #ffffff; }
        .data-table tbody tr:nth-child(even) { background: #f9fafb; }
        .data-table tbody tr:hover { background: #ecfdf5; }
        .data-table tfoot td { 
            font-weight: 700; 
            background: linear-gradient(135deg, #f0fdf4 0%, #d1fae5 100%);
            border-top: 3px solid #047857;
            padding: 12px 10px;
            font-size: 10px;
            color: #065f46;
        }

        /* Status Badges Profissionais */
        .badge { 
            display: inline-block; 
            padding: 4px 12px; 
            border-radius: 15px; 
            font-size: 8px; 
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        .badge-green { background: #d1fae5; color: #065f46; border: 1.5px solid #10b981; }
        .badge-red { background: #fee2e2; color: #991b1b; border: 1.5px solid #ef4444; }
        .badge-amber { background: #fef3c7; color: #92400e; border: 1.5px solid #f59e0b; }
        .badge-blue { background: #dbeafe; color: #1e40af; border: 1.5px solid #3b82f6; }
        .badge-purple { background: #f3e8ff; color: #6b21a8; border: 1.5px solid #a855f7; }
        .badge-gray { background: #f3f4f6; color: #374151; border: 1.5px solid #9ca3af; }

        /* Summary Cards Melhorados */
        .summary { 
            width: 100%; 
            border-collapse: separate;
            border-spacing: 12px;
            margin-bottom: 25px; 
        }
        .summary td { 
            background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
            padding: 18px; 
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
            border: 2px solid #e5e7eb;
            position: relative;
            overflow: hidden;
        }
        .summary td::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #047857 0%, #10b981 100%);
        }
        .summary .label { 
            font-size: 9px; 
            text-transform: uppercase; 
            color: #6b7280;
            font-weight: 700;
            letter-spacing: 0.8px;
            margin-bottom: 10px;
        }
        .summary .value { 
            font-size: 26px; 
            font-weight: 900; 
            color: #111827;
            line-height: 1;
            text-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        /* Colors Profissionais */
        .text-green { color: #047857; font-weight: 700; }
        .text-red { color: #dc2626; font-weight: 700; }
        .text-amber { color: #d97706; font-weight: 700; }
        .text-blue { color: #2563eb; font-weight: 700; }
        .text-purple { color: #7c3aed; font-weight: 700; }
        .bg-red-light { background: #fef2f2; }
        .bg-amber-light { background: #fffbeb; }
        .bg-blue-light { background: #eff6ff; }
        .bg-green-light { background: #f0fdf4; }

        /* Section Headers Profissionais */
        .section-title { 
            font-size: 12px; 
            font-weight: 800; 
            text-transform: uppercase; 
            color: #065f46; 
            border-left: 6px solid #047857;
            background: linear-gradient(90deg, #ecfdf5 0%, #d1fae5 50%, #ecfdf5 100%);
            padding: 12px 20px; 
            margin: 20px 0 15px;
            border-radius: 6px;
            letter-spacing: 1.2px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            border-top: 2px solid #10b981;
            border-bottom: 2px solid #10b981;
        }

        /* Footer Profissional */
        .footer { 
            margin-top: 35px; 
            text-align: center; 
            font-size: 8px; 
            color: #6b7280; 
            border-top: 3px double #047857; 
            padding-top: 15px;
            line-height: 1.8;
        }
        .footer strong {
            color: #065f46;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Page Break */
        .page-break { page-break-after: always; }

        /* Alert Boxes Profissionais */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 10px;
            font-weight: 600;
            line-height: 1.6;
            box-shadow: 0 3px 8px rgba(0,0,0,0.08);
        }
        .alert-warning {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border-left: 5px solid #f59e0b;
            border-top: 2px solid #fbbf24;
            border-bottom: 2px solid #fbbf24;
            color: #78350f;
        }
        .alert-danger {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border-left: 5px solid #ef4444;
            border-top: 2px solid #f87171;
            border-bottom: 2px solid #f87171;
            color: #7f1d1d;
        }
        .alert-info {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-left: 5px solid #3b82f6;
            border-top: 2px solid #60a5fa;
            border-bottom: 2px solid #60a5fa;
            color: #1e3a8a;
        }
        .alert strong {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Progress Bar Profissional */
        .progress {
            background: #e5e7eb;
            height: 22px;
            border-radius: 11px;
            overflow: hidden;
            margin: 10px 0;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
            border: 1px solid #d1d5db;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #047857 0%, #10b981 50%, #34d399 100%);
            text-align: center;
            color: white;
            font-size: 9px;
            font-weight: 700;
            line-height: 22px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
            letter-spacing: 0.5px;
        }
    </style>
    @yield('styles')
</head>
<body>
<div class="page">

    {{-- Cabeçalho Institucional Completo --}}
    <div class="header">
        <div class="header-top">
            <div class="header-top-content">
                <div class="header-brasao">
                    <div class="brasao-container">
                        <div class="brasao-text">🇧🇷</div>
                    </div>
                </div>
                <div class="header-institucional">
                    <h1>MINISTÉRIO DA DEFESA — EXÉRCITO BRASILEIRO</h1>
                    <h2>11º Depósito de Suprimento — Seção de Intendência</h2>
                    <div class="subtitle">Comando Logístico — Sistema Integrado de Controle de Material</div>
                </div>
                <div class="header-sistema">
                    <div class="sistema-badge">SUBRAVO</div>
                </div>
            </div>
        </div>
        <div class="header-main">
            <div style="text-align: center; font-size: 10px; font-weight: 600; letter-spacing: 1px; opacity: 0.95;">
                Sistema de Controle de Estoque e Empréstimo de Material de Intendência
            </div>
        </div>
    </div>

    {{-- Título do Relatório --}}
    <div class="report-title">
        @yield('report-title', 'Relatório')
    </div>

    {{-- Metadados do Relatório --}}
    <div class="meta">
        <div class="meta-left">
            <strong>Data de Geração:</strong> @yield('meta-date', $generatedAt ?? now()->format('d/m/Y H:i'))<br>
            <strong>Gerado por:</strong> @yield('meta-user', $generatedBy ?? 'Sistema Automatizado')
        </div>
        <div class="meta-right">
            @yield('meta-right')
            @if(isset($dateFrom) && isset($dateTo))
                <strong>Período Analisado:</strong> {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
            @endif
        </div>
    </div>

    {{-- Conteúdo do Relatório --}}
    @yield('content')

    {{-- Rodapé Institucional --}}
    <div class="footer">
        <strong>SUBRAVO</strong> — Sistema de Controle de Estoque e Empréstimo de Material de Intendência<br>
        Documento gerado automaticamente em {{ now()->format('d/m/Y \à\s H:i:s') }} — {{ now()->timezone->getName() }}<br>
        Este documento possui validade jurídica conforme legislação vigente do Exército Brasileiro<br>
        <strong>Classificação:</strong> OSTENSIVO — <strong>Uso:</strong> INTERNO
    </div>

</div>
</body>
</html>
