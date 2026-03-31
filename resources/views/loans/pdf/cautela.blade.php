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
        .receipt-grid { margin-top: 12px; }
        .receipt-line { margin-top: 6px; font-size: 11px; line-height: 1.8; }
        .date-line { margin-top: 18px; text-align: center; font-size: 11px; }

        /* ── Data devolução ── */
        .return-date { font-size: 11px; margin-top: 6px; }

        /* ── Assinatura ── */
        .signature-block { margin-top: 34px; text-align: center; }
        .sig-line { margin: 0 auto; width: 70%; padding-top: 4px; }
        .sig-role { font-size: 9px; color: #555; text-transform: uppercase; margin-top: 2px; }

        /* ── Observações ── */
        .notes { font-size: 10px; color: #555; margin-bottom: 14px; padding: 6px 8px; background: #fafafa; border: 1px solid #ddd; }

        /* ── Rodapé ── */
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #ddd; padding-top: 5px; }
    </style>
</head>
<body>
<div class="page">
    @php
        $borrowerName = $loan->isIndividual() ? $loan->getBorrowerDisplayName() : ($loan->borrower_section ?: '');
        $borrowerIdentity = $loan->getBorrowerIdentityDisplay() ?? '';
        $borrowerRank = $loan->getBorrowerRankDisplay() ?? '';
        $borrowerWarName = $loan->getBorrowerWarNameDisplay() ?? '';
        $signerName = $loan->getSignerDisplayName();
        $signerRank = $loan->getSignerRankDisplay() ?? '';
        $signerWarName = $loan->getSignerWarNameDisplay() ?? '';
        $signerIdentity = $loan->getSignerIdentityDisplay() ?? '';
        $hasSignerDetails = $loan->hasSignerDetails();
        $signatureDisplay = $loan->getSignatureDisplay();
        $borrowerFields = array_filter([
            ['label' => 'Nome de Guerra', 'value' => $borrowerWarName],
            ['label' => 'Posto/Grad', 'value' => $borrowerRank],
            ['label' => 'Idt', 'value' => $borrowerIdentity],
            ['label' => 'CPF', 'value' => $loan->borrower_cpf ?? ''],
            ['label' => 'Contato', 'value' => $loan->borrower_phone ?? ''],
            ['label' => 'OM', 'value' => $loan->borrowerOrganization?->abbreviation ?? ''],
        ], fn ($field) => filled($field['value']));
        $signerFields = array_filter([
            ['label' => 'Assina Cautela', 'value' => $signerName],
            ['label' => 'Nome de Guerra', 'value' => $signerWarName],
            ['label' => 'Posto/Grad', 'value' => $signerRank],
            ['label' => 'Idt', 'value' => $signerIdentity],
            ['label' => 'CPF', 'value' => $loan->signer_cpf ?? ''],
            ['label' => 'Contato', 'value' => $loan->signer_phone ?? ''],
        ], fn ($field) => filled($field['value']));
    @endphp

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
    <div class="receipt-grid">
        <div class="receipt-line">
            <strong>Cautelado para:</strong> {{ $borrowerName }}
        </div>

        @foreach($borrowerFields as $field)
            <div class="receipt-line"><strong>{{ $field['label'] }}:</strong> {{ $field['value'] }}</div>
        @endforeach

        @if($hasSignerDetails)
            @foreach($signerFields as $field)
                <div class="receipt-line"><strong>{{ $field['label'] }}:</strong> {{ $field['value'] }}</div>
            @endforeach
        @endif
    </div>

    {{-- ── Data prevista ── --}}
    <div class="date-line">
        ______-____, _____ de _________, de 20___
    </div>

    {{-- ── Assinatura ── --}}
    <div class="signature-block">
        <br><br><br>
        <div class="sig-line">
            <div style="font-weight:bold; font-size:11px;">
                {{ $signatureDisplay }}
            </div>
            <div class="sig-role">Assinatura do Responsável</div>
        </div>
    </div>

    {{-- ── Rodapé ── --}}
    <div class="footer">
        Documento gerado pelo Sistema HelpSub em {{ now()->format('d/m/Y \a\s H:i') }}
        — Este documento não possui validade sem a assinatura acima.
    </div>

</div>
</body>
</html>
