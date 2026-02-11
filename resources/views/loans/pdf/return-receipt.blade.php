<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Devolução — {{ $loan->loan_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1a1a1a; line-height: 1.4; }
        .page { padding: 20px 30px; }

        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px; }
        .header h2 { font-size: 12px; font-weight: normal; color: #555; }
        .header .subtitle { font-size: 10px; color: #777; margin-top: 4px; }

        .doc-title { text-align: center; font-size: 14px; font-weight: bold; margin: 12px 0; padding: 6px; background: #f0f0f0; border: 1px solid #ccc; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-table td { padding: 3px 8px; font-size: 10px; vertical-align: top; }
        .info-table .lbl { font-weight: bold; color: #555; text-transform: uppercase; width: 25%; }

        .section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #333; border-bottom: 1px solid #999; padding-bottom: 3px; margin: 15px 0 8px; }

        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .items-table th { background: #e8e8e8; border: 1px solid #999; padding: 5px 6px; font-size: 9px; text-transform: uppercase; text-align: center; font-weight: bold; }
        .items-table td { border: 1px solid #bbb; padding: 4px 6px; font-size: 10px; }
        .items-table td.center { text-align: center; }

        .cond-ok { color: #16a34a; font-weight: bold; }
        .cond-warn { color: #d97706; font-weight: bold; }
        .cond-bad { color: #dc2626; font-weight: bold; }

        .notes { font-size: 10px; color: #555; margin-top: 10px; padding: 6px 8px; background: #fafafa; border: 1px solid #ddd; }
        .notes strong { color: #333; }

        .signatures { width: 100%; margin-top: 40px; border-collapse: collapse; }
        .signatures td { width: 50%; text-align: center; padding: 0 20px; vertical-align: bottom; }
        .sig-line { border-top: 1px solid #333; margin: 0 10px; padding-top: 4px; }
        .sig-name { font-weight: bold; font-size: 11px; }
        .sig-role { font-size: 9px; color: #555; text-transform: uppercase; }

        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #ddd; padding-top: 5px; }
    </style>
</head>
<body>
<div class="page">

    {{-- Cabeçalho --}}
    <div class="header">
        <h1>MINISTÉRIO DA DEFESA</h1>
        <h2>{{ $loan->borrowerOrganization?->name ?? 'Organização Militar' }}</h2>
        <div class="subtitle">Sistema SUBRAVO — Controle de Material</div>
    </div>

    <div class="doc-title">
        RECIBO DE DEVOLUÇÃO DE MATERIAL — Cautela Nº {{ $loan->loan_number }}
    </div>

    {{-- Informações Básicas --}}
    <table class="info-table">
        <tr>
            <td class="lbl">Cautela Nº:</td>
            <td>{{ $loan->loan_number }}</td>
            <td class="lbl">Data Empréstimo:</td>
            <td>{{ $loan->loan_date->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="lbl">Mutuário:</td>
            <td>
                @if($loan->borrower_type === 'individual')
                    {{ $loan->borrower?->getDisplayName() ?? '—' }}
                    ({{ $loan->borrower?->identity_number ?? '' }})
                @else
                    {{ $loan->borrower_section }}
                @endif
            </td>
            <td class="lbl">Data Devolução:</td>
            <td>{{ $loan->actual_return_date?->format('d/m/Y') ?? now()->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="lbl">OM:</td>
            <td>{{ $loan->borrowerOrganization?->abbreviation ?? '—' }}</td>
            <td class="lbl">Recebido por:</td>
            <td>{{ $loan->returnedTo?->getDisplayName() ?? '—' }}</td>
        </tr>
    </table>

    {{-- Itens Devolvidos --}}
    <div class="section-title">Itens Devolvidos</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 30%">Descrição</th>
                <th style="width: 12%">Lote / Série</th>
                <th style="width: 8%">Cautelado</th>
                <th style="width: 8%">Devolvido</th>
                <th style="width: 12%">Cond. Saída</th>
                <th style="width: 12%">Cond. Retorno</th>
                <th style="width: 13%">Obs</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loan->items as $i => $item)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $item->stockItem?->product?->name ?? 'N/D' }}</td>
                    <td class="center">
                        {{ $item->stockItem?->batch ?? '' }}
                        {{ $item->stockItem?->serial_number ? 'Nº ' . $item->stockItem->serial_number : '' }}
                    </td>
                    <td class="center">{{ $item->quantity }}</td>
                    <td class="center">{{ $item->returned_quantity }}</td>
                    <td class="center">{{ ucfirst($item->condition_out) }}</td>
                    <td class="center">
                        @if($item->condition_in)
                            <span class="{{ $item->condition_in === 'bom' ? 'cond-ok' : ($item->condition_in === 'regular' ? 'cond-warn' : 'cond-bad') }}">
                                {{ ucfirst($item->condition_in) }}
                            </span>
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if($item->returned_quantity < $item->quantity)
                            Falta: {{ $item->quantity - $item->returned_quantity }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Resumo --}}
    <table class="info-table" style="background: #f5f5f5; border: 1px solid #ddd; padding: 5px;">
        <tr>
            <td class="lbl">Status Final:</td>
            <td>
                @if($loan->status === 'returned')
                    <strong style="color: #16a34a;">TOTALMENTE DEVOLVIDA</strong>
                @else
                    <strong style="color: #d97706;">PARCIALMENTE DEVOLVIDA</strong>
                @endif
            </td>
            <td class="lbl">Total Itens:</td>
            <td>{{ $loan->items->sum('returned_quantity') }} / {{ $loan->items->sum('quantity') }}</td>
        </tr>
    </table>

    {{-- Observações --}}
    @if($loan->return_notes)
        <div class="notes">
            <strong>Observações da Devolução:</strong> {{ $loan->return_notes }}
        </div>
    @endif

    {{-- Assinaturas --}}
    <table class="signatures">
        <tr>
            <td>
                <div style="height: 50px;"></div>
                <div class="sig-line">
                    @if($loan->borrower_type === 'individual')
                        <div class="sig-name">{{ $loan->borrower?->getDisplayName() ?? '________________________' }}</div>
                    @else
                        <div class="sig-name">________________________</div>
                    @endif
                    <div class="sig-role">Mutuário / Responsável pela Devolução</div>
                </div>
            </td>
            <td>
                <div style="height: 50px;"></div>
                <div class="sig-line">
                    <div class="sig-name">{{ $loan->returnedTo?->getDisplayName() ?? '________________________' }}</div>
                    <div class="sig-role">Almoxarife / Responsável pelo Recebimento</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Documento gerado pelo Sistema SUBRAVO em {{ now()->format('d/m/Y \à\s H:i') }}
        &mdash; Este documento não possui validade sem as assinaturas acima.
    </div>

</div>
</body>
</html>
