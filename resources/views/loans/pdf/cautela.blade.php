<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cautela {{ $loan->loan_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1a1a1a; line-height: 1.5; }
        .page { padding: 24px 32px; }

        /* ── Cabeçalho ── */
        .header { text-align: center; border-bottom: 2px solid #1a1a1a; padding-bottom: 12px; margin-bottom: 14px; }
        .header p { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.6; }
        .header .title-cautela { font-size: 15px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; margin-top: 6px; border-top: 1px solid #555; padding-top: 6px; }

        /* ── Número da cautela ── */
        .cautela-number { text-align: center; font-size: 12px; font-weight: bold; margin: 10px 0; padding: 5px 8px; background: #f0f0f0; border: 1px solid #bbb; }

        /* ── Texto inicial ── */
        .intro-text { font-size: 11px; text-align: justify; margin-bottom: 16px; line-height: 1.7; }

        /* ── Tabela de itens ── */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .items-table th { background: #e0e0e0; border: 1px solid #888; padding: 5px 6px; font-size: 10px; text-transform: uppercase; text-align: center; font-weight: bold; }
        .items-table td { border: 1px solid #aaa; padding: 5px 6px; font-size: 10px; }
        .items-table td.center { text-align: center; }
        .items-table tfoot td { font-weight: bold; background: #f5f5f5; border: 1px solid #aaa; }

        /* ── Rodapé de itens ── */
        .receipt-line { margin-top: 12px; font-size: 11px; line-height: 2.2; }
        .receipt-line .line { border-bottom: 1px solid #333; display: inline-block; min-width: 160px; }

        /* ── Data devolução ── */
        .return-date { font-size: 11px; margin-top: 6px; }

        /* ── Assinatura ── */
        .signature-block { margin-top: 40px; text-align: center; }
        .sig-line { border-top: 1px solid #333; margin: 0 auto; width: 60%; padding-top: 4px; }
        .sig-role { font-size: 9px; color: #555; text-transform: uppercase; margin-top: 2px; }

        /* ── Observações ── */
        .notes { font-size: 10px; color: #555; margin-bottom: 14px; padding: 6px 8px; background: #fafafa; border: 1px solid #ddd; }

        /* ── Rodapé ── */
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #ddd; padding-top: 5px; }
    </style>
</head>
<body>
<div class="page">

    {{-- ── Cabeçalho institucional ── --}}
    <div class="header">
        <p>MINISTÉRIO DA DEFESA</p>
        <p>EXÉRCITO BRASILEIRO</p>
        <p>CMP 11ª REGIÃO MILITAR</p>
        <p>11º DEPÓSITO DE SUPRIMENTO</p>
        <p>(Depósito Marechal Mário Travassos)</p>
        @if($loan->subunit)
            <p>{{ strtoupper($loan->subunit) }}</p>
        @endif
        <div class="title-cautela">CAUTELA</div>
    </div>

    {{-- ── Número da cautela ── --}}
    <div class="cautela-number">
        Nº {{ $loan->loan_number }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        Data de Emissão: {{ $loan->loan_date->format('d/m/Y') }}
    </div>

    {{-- ── Texto introdutório ── --}}
    <div class="intro-text">
        Recebi a título de empréstimo do encarregado de Material
        da(o) <strong>{{ $loan->subunit ? strtoupper($loan->subunit) : '___________________' }}</strong>,
        o(s) artigo(s) especificado(s) abaixo e me responsabilizo pelos mesmos,
        a partir da presente data:
    </div>

    {{-- ── Tabela de Itens ── --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 8%">Qtd</th>
                <th style="width: 72%">Descrição do Material</th>
                <th style="width: 20%">Obs</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loan->items as $item)
                <tr>
                    <td class="center">{{ $item->quantity }}</td>
                    <td>
                        {{ $item->stockItem?->product?->name ?? 'N/D' }}
                        @if($item->stockItem?->batch)
                            — Lote: {{ $item->stockItem->batch }}
                        @endif
                        @if($item->stockItem?->serial_number)
                            — Nº {{ $item->stockItem->serial_number }}
                        @endif
                    </td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── Observações ── --}}
    @if($loan->notes)
        <div class="notes"><strong>Observações:</strong> {{ $loan->notes }}</div>
    @endif

    {{-- ── Recebido por / Identidade ── --}}
    <div class="receipt-line">
        Recebido por:&nbsp;<span class="line">
            @if($loan->borrower_type === 'individual' && $loan->borrower)
                {{ $loan->borrower->getDisplayName() }}
            @endif
        </span>
        &nbsp;&nbsp;&nbsp;&nbsp;
        Idt:&nbsp;<span class="line">
            @if($loan->borrower_type === 'individual' && $loan->borrower)
                {{ $loan->borrower->identity_number }}
            @endif
        </span>
    </div>

    {{-- ── Data prevista ── --}}
    <div class="return-date">
        Data prevista para devolução:&nbsp;
        @if($loan->expected_return_date)
            {{ $loan->expected_return_date->format('d/m/Y') }}
        @else
            _____ / ____________ / __________
        @endif
    </div>

    {{-- ── Assinatura ── --}}
    <div class="signature-block">
        <br><br><br>
        <div class="sig-line">
            @if($loan->borrower_type === 'individual' && $loan->borrower)
                <div style="font-weight:bold; font-size:11px;">
                    {{ $loan->borrower->getDisplayName() }}
                </div>
            @endif
            <div class="sig-role">Assinatura do Responsável</div>
        </div>
    </div>

    {{-- ── Rodapé ── --}}
    <div class="footer">
        Documento gerado pelo Sistema SMARTSUB em {{ now()->format('d/m/Y \à\s H:i') }}
        — Este documento não possui validade sem a assinatura acima.
    </div>

</div>
</body>
</html>
