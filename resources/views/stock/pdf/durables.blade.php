<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relação de Material de Uso Duradouro</title>
    <style>
        @page {
            margin: 18mm 15mm 18mm 15mm;
            size: A4 landscape;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            color: #111827;
            line-height: 1.5;
            background: white;
        }

        /* ── Header ─────────────────────────────────────────────── */
        .header {
            background: linear-gradient(135deg, #065f46 0%, #047857 50%, #059669 100%);
            color: white;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        .header-inner {
            display: table;
            width: 100%;
        }
        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 60%;
        }
        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 40%;
        }
        .header-om {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #a7f3d0;
            margin-bottom: 2px;
        }
        .header-title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        .header-subtitle {
            font-size: 8px;
            color: #d1fae5;
            margin-top: 3px;
        }
        .header-meta {
            font-size: 8px;
            color: #d1fae5;
            line-height: 1.6;
        }
        .header-meta strong {
            color: white;
        }
        .smartsub-badge {
            display: inline-block;
            background: linear-gradient(135deg, #d97706, #f59e0b);
            color: white;
            font-size: 7px;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        /* ── Resumo ──────────────────────────────────────────────── */
        .summary-bar {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 4px 0;
            margin-bottom: 10px;
        }
        .summary-card {
            display: table-cell;
            background: #f0fdf4;
            border: 1px solid #a7f3d0;
            border-radius: 5px;
            padding: 7px 8px;
            text-align: center;
            vertical-align: middle;
        }
        .summary-card.warn {
            background: #fffbeb;
            border-color: #fde68a;
        }
        .summary-card.danger {
            background: #fef2f2;
            border-color: #fecaca;
        }
        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #064e3b;
        }
        .summary-card.warn .summary-value { color: #92400e; }
        .summary-card.danger .summary-value { color: #991b1b; }
        .summary-label {
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            margin-top: 1px;
        }

        /* ── Tabela ──────────────────────────────────────────────── */
        .table-wrap {
            margin-bottom: 12px;
        }
        .section-title {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #065f46;
            border-left: 4px solid #059669;
            padding: 3px 8px;
            background: #ecfdf5;
            border-radius: 0 4px 4px 0;
            margin-bottom: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead tr {
            background: linear-gradient(135deg, #065f46, #047857);
            color: white;
        }
        thead th {
            padding: 6px 8px;
            text-align: left;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        thead th.center { text-align: center; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tbody tr:nth-child(odd)  { background: #ffffff; }
        tbody td {
            padding: 5px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 8.5px;
            vertical-align: middle;
        }
        tbody td.center { text-align: center; }
        tbody td.num {
            text-align: center;
            font-weight: bold;
        }
        .n-col { width: 3%; color: #9ca3af; font-size: 8px; }
        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: bold;
        }
        .badge-warn {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .badge-ok {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        /* ── Rodapé ──────────────────────────────────────────────── */
        .footer {
            margin-top: 14px;
            border-top: 2px solid #059669;
            padding-top: 8px;
        }
        .footer-inner {
            display: table;
            width: 100%;
        }
        .footer-left { display: table-cell; width: 50%; }
        .footer-right { display: table-cell; width: 50%; text-align: right; }
        .footer-text {
            font-size: 7.5px;
            color: #6b7280;
            line-height: 1.7;
        }
        .sign-block {
            margin-top: 20px;
            border-top: 1px solid #374151;
            width: 200px;
            padding-top: 4px;
            font-size: 7.5px;
            color: #374151;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- ── Cabeçalho ──────────────────────────────────────────── --}}
    <div class="header">
        <div class="header-inner">
            <div class="header-left">
                <div class="smartsub-badge">SMARTSUB</div>
                <div class="header-om">Ministério da Defesa — Exército Brasileiro</div>
                <div class="header-title">Relação de Material de Uso Duradouro</div>
                <div class="header-subtitle">Controle individualizado de material permanente / bens duráveis</div>
            </div>
            <div class="header-right">
                <div class="header-meta">
                    <strong>Data de emissão:</strong> {{ now()->format('d/m/Y \à\s H:i') }}<br>
                    <strong>Gerado por:</strong> {{ auth()->user()->getDisplayName() }}<br>
                    @if(auth()->user()->organization)
                        <strong>OM:</strong> {{ auth()->user()->organization->abbreviation }}<br>
                    @endif
                    @if(auth()->user()->subunit)
                        <strong>Subunidade:</strong> {{ auth()->user()->subunit }}<br>
                    @endif
                    <strong>Total de produtos:</strong> {{ $durableItems->count() }}
                </div>
            </div>
        </div>
    </div>

    {{-- ── Resumo geral ─────────────────────────────────────── --}}
    @php
        $totalSiscofis  = $durableItems->sum('inventory_qty');
        $totalItems     = $durableItems->sum('total');
        $totalAvailable = $durableItems->sum('available');
        $totalLoaned    = $durableItems->sum('loaned');
        $totalDamaged   = $durableItems->sum('damaged');
        $totalExpiring  = $durableItems->sum('expiring_soon');
        $totalExpired   = $durableItems->sum('expired');
    @endphp
    <table class="summary-bar">
        <tr>
            <td class="summary-card">
                <div class="summary-value">{{ number_format($totalSiscofis, 0, ',', '.') }}</div>
                <div class="summary-label">Inventário SISCOFIS</div>
            </td>
            <td class="summary-card">
                <div class="summary-value">{{ number_format($totalItems, 0, ',', '.') }}</div>
                <div class="summary-label">Estoque Controlado</div>
            </td>
            <td class="summary-card">
                <div class="summary-value" style="color:#1d4ed8;">{{ number_format($totalAvailable, 0, ',', '.') }}</div>
                <div class="summary-label">Disponíveis</div>
            </td>
            <td class="summary-card">
                <div class="summary-value" style="color:#1e40af;">{{ number_format($totalLoaned, 0, ',', '.') }}</div>
                <div class="summary-label">Emprestados</div>
            </td>
            @if($totalDamaged > 0)
            <td class="summary-card danger">
                <div class="summary-value">{{ number_format($totalDamaged, 0, ',', '.') }}</div>
                <div class="summary-label">Danificados</div>
            </td>
            @endif
            @if($totalExpiring + $totalExpired > 0)
            <td class="summary-card warn">
                <div class="summary-value">{{ $totalExpiring + $totalExpired }}</div>
                <div class="summary-label">Vencidos/Vencendo</div>
            </td>
            @endif
        </tr>
    </table>

    {{-- ── Tabela de produtos ───────────────────────────────── --}}
    <div class="table-wrap">
        <div class="section-title">Listagem de Material de Uso Duradouro — {{ now()->format('d/m/Y') }}</div>
        <table>
            <thead>
                <tr>
                    <th class="n-col center">Nº</th>
                    <th style="width:28%">Produto</th>
                    <th style="width:10%">Cód. SISCOFIS</th>
                    <th style="width:14%">Categoria</th>
                    <th class="center" style="width:9%">Inv. SISCOFIS</th>
                    <th class="center" style="width:7%">Total Est.</th>
                    <th class="center" style="width:7%">Disponíveis</th>
                    <th class="center" style="width:7%">Emprestados</th>
                    <th class="center" style="width:7%">Danificados</th>
                    <th class="center" style="width:10%">Situação</th>
                </tr>
            </thead>
            <tbody>
                @forelse($durableItems as $index => $item)
                    @php
                        $hasAlert  = $item->expired > 0 || $item->expiring_soon > 0;
                        $noStock   = $item->total === 0;
                        $diff      = $item->inventory_qty > 0 ? ($item->total - $item->inventory_qty) : null;
                    @endphp
                    <tr>
                        <td class="n-col center">{{ $index + 1 }}</td>
                        <td style="font-weight:600;">
                            {{ $item->product->name }}
                            @if($item->product->shelf_life_months)
                                <br><span style="font-weight:normal;color:#6b7280;font-size:7.5px;">Validade: {{ $item->product->shelf_life_months }} meses</span>
                            @endif
                        </td>
                        <td style="font-family:monospace;color:#374151;">
                            {{ $item->product->siscofis_code ?? '—' }}
                        </td>
                        <td style="color:#6b7280;">{{ $item->product->category->name }}</td>
                        <td class="num">
                            @if($item->inventory_qty > 0)
                                {{ number_format($item->inventory_qty, 0, ',', '.') }}
                            @else
                                <span style="color:#d1d5db;">—</span>
                            @endif
                        </td>
                        <td class="num">{{ $item->total }}</td>
                        <td class="num" style="color:#166534;">{{ $item->available }}</td>
                        <td class="num" style="color:#1e40af;">{{ $item->loaned }}</td>
                        <td class="num" style="{{ $item->damaged > 0 ? 'color:#991b1b;' : 'color:#9ca3af;' }}">
                            {{ $item->damaged ?: '—' }}
                        </td>
                        <td class="center">
                            @if($item->expired > 0)
                                <span class="badge badge-danger">{{ $item->expired }} vencido(s)</span>
                            @elseif($item->expiring_soon > 0)
                                <span class="badge badge-warn">{{ $item->expiring_soon }} vencendo</span>
                            @elseif($noStock)
                                <span class="badge" style="background:#f3f4f6;color:#6b7280;border:1px solid #e5e7eb;">Sem estoque</span>
                            @else
                                <span class="badge badge-ok">Regular</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align:center;padding:20px;color:#9ca3af;">
                            Nenhum produto de uso duradouro cadastrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Rodapé ───────────────────────────────────────────── --}}
    <div class="footer">
        <div class="footer-inner">
            <div class="footer-left">
                <div class="footer-text">
                    <strong>SMARTSUB</strong> — Sistema de Controle de Material de Intendência<br>
                    Documento gerado em {{ now()->setTimezone('America/Sao_Paulo')->format('d/m/Y \à\s H:i:s') }} (BRT)<br>
                    <strong>Classificação:</strong> OSTENSIVO — USO INTERNO
                </div>
            </div>
            <div class="footer-right">
                <div class="sign-block">
                    {{ auth()->user()->getDisplayName() }}<br>
                    Responsável pelo Almoxarifado
                </div>
            </div>
        </div>
    </div>

</body>
</html>
