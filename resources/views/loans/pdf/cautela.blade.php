<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cautela {{ $loan->loan_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1a1a1a; line-height: 1.4; }
        .page { padding: 20px 30px; }

        /* Header */
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px; }
        .header h2 { font-size: 12px; font-weight: normal; color: #555; }
        .header .subtitle { font-size: 10px; color: #777; margin-top: 4px; }

        /* Cautela Number */
        .cautela-number { text-align: center; font-size: 16px; font-weight: bold; margin: 12px 0; padding: 6px; background: #f0f0f0; border: 1px solid #ccc; }

        /* Info Grid */
        .info-grid { display: table; width: 100%; margin-bottom: 15px; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; width: 30%; padding: 3px 8px; font-weight: bold; font-size: 10px; color: #555; text-transform: uppercase; vertical-align: top; }
        .info-value { display: table-cell; width: 70%; padding: 3px 8px; font-size: 11px; }

        /* Two columns */
        .cols { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .cols td { width: 50%; vertical-align: top; padding: 0 5px; }

        /* Section title */
        .section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #333; border-bottom: 1px solid #999; padding-bottom: 3px; margin-bottom: 8px; }

        /* Items Table */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .items-table th { background: #e8e8e8; border: 1px solid #999; padding: 5px 6px; font-size: 9px; text-transform: uppercase; text-align: center; font-weight: bold; }
        .items-table td { border: 1px solid #bbb; padding: 4px 6px; font-size: 10px; }
        .items-table td.center { text-align: center; }
        .items-table tfoot td { font-weight: bold; background: #f5f5f5; }

        /* Signatures */
        .signatures { width: 100%; margin-top: 40px; border-collapse: collapse; }
        .signatures td { width: 50%; text-align: center; padding: 0 20px; vertical-align: bottom; }
        .sig-line { border-top: 1px solid #333; margin: 0 10px; padding-top: 4px; }
        .sig-name { font-weight: bold; font-size: 11px; }
        .sig-role { font-size: 9px; color: #555; text-transform: uppercase; }

        /* Footer */
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #ddd; padding-top: 5px; }

        /* Notes */
        .notes { font-size: 10px; color: #555; margin-top: 10px; padding: 6px 8px; background: #fafafa; border: 1px solid #ddd; }
        .notes strong { color: #333; }
    </style>
</head>
<body>
<div class="page">

    {{-- Cabeçalho Militar --}}
    <div class="header">
        <h1>MINISTÉRIO DA DEFESA</h1>
        <h2>{{ $loan->borrowerOrganization?->name ?? 'Organização Militar' }}</h2>
        <div class="subtitle">Sistema SMARTSUB — Controle de Material</div>
    </div>

    <div class="cautela-number">
        CAUTELA DE MATERIAL Nº {{ $loan->loan_number }}
    </div>

    {{-- Informações lado a lado --}}
    <table class="cols">
        <tr>
            <td>
                <div class="section-title">Dados da Cautela</div>
                <div class="info-grid">
                    <div class="info-row">
                        <span class="info-label">Data Emissão:</span>
                        <span class="info-value">{{ $loan->loan_date->format('d/m/Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Previsão Devol.:</span>
                        <span class="info-value">{{ $loan->expected_return_date?->format('d/m/Y') ?? 'Indeterminada' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Expedido por:</span>
                        <span class="info-value">{{ $loan->issuedBy?->getDisplayName() ?? '—' }}</span>
                    </div>
                </div>
            </td>
            <td>
                <div class="section-title">Mutuário</div>
                <div class="info-grid">
                    @if($loan->borrower_type === 'individual')
                        <div class="info-row">
                            <span class="info-label">Nome:</span>
                            <span class="info-value">{{ $loan->borrower?->getDisplayName() ?? '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Identidade:</span>
                            <span class="info-value">{{ $loan->borrower?->identity_number ?? '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Posto/Grad.:</span>
                            <span class="info-value">{{ $loan->borrower?->rank?->abbreviation ?? '—' }}</span>
                        </div>
                    @else
                        <div class="info-row">
                            <span class="info-label">Seção:</span>
                            <span class="info-value">{{ $loan->borrower_section }}</span>
                        </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">OM:</span>
                        <span class="info-value">{{ $loan->borrowerOrganization?->abbreviation ?? '—' }}</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Tabela de Itens --}}
    <div class="section-title">Itens Cautelados</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 35%">Descrição do Material</th>
                <th style="width: 15%">Lote / Série</th>
                <th style="width: 10%">Qtd</th>
                <th style="width: 10%">Unidade</th>
                <th style="width: 12%">Condição</th>
                <th style="width: 13%">Observação</th>
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
                    <td class="center">{{ $item->stockItem?->product?->unit ?? '—' }}</td>
                    <td class="center">{{ ucfirst($item->condition_out) }}</td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right;">Total de Itens:</td>
                <td class="center">{{ $loan->items->sum('quantity') }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

    {{-- Observações --}}
    @if($loan->notes)
        <div class="notes">
            <strong>Observações:</strong> {{ $loan->notes }}
        </div>
    @endif

    {{-- Assinaturas --}}
    <table class="signatures">
        <tr>
            <td>
                <div style="height: 50px;"></div>
                <div class="sig-line">
                    <div class="sig-name">{{ $loan->issuedBy?->getDisplayName() ?? '________________________' }}</div>
                    <div class="sig-role">Almoxarife / Responsável pela Entrega</div>
                </div>
            </td>
            <td>
                <div style="height: 50px;"></div>
                <div class="sig-line">
                    @if($loan->borrower_type === 'individual')
                        <div class="sig-name">{{ $loan->borrower?->getDisplayName() ?? '________________________' }}</div>
                    @else
                        <div class="sig-name">________________________</div>
                    @endif
                    <div class="sig-role">Mutuário / Responsável pelo Recebimento</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Rodapé --}}
    <div class="footer">
        Documento gerado pelo Sistema SMARTSUB em {{ now()->format('d/m/Y \à\s H:i') }}
        &mdash; Este documento não possui validade sem as assinaturas acima.
    </div>

</div>
</body>
</html>
