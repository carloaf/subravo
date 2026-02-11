<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Etiqueta — {{ $stockItem->product?->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; }

        @media print {
            @page { margin: 0; size: 80mm 50mm; }
            body { margin: 0; }
        }

        .label {
            width: 80mm;
            height: 50mm;
            padding: 3mm;
            border: 1px solid #333;
            display: table;
            page-break-after: always;
        }

        .label-left {
            display: table-cell;
            vertical-align: top;
            width: 50mm;
            padding-right: 3mm;
        }

        .label-right {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            width: 27mm;
        }

        .product-name {
            font-size: 11px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 2mm;
            line-height: 1.2;
            max-height: 10mm;
            overflow: hidden;
        }

        .label-line {
            font-size: 8px;
            color: #333;
            margin-bottom: 1mm;
            line-height: 1.3;
        }

        .label-line .lbl {
            font-weight: bold;
            color: #555;
            text-transform: uppercase;
            font-size: 7px;
        }

        .system-tag {
            font-size: 6px;
            color: #999;
            margin-top: 2mm;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .qr-code img,
        .qr-code svg {
            width: 25mm !important;
            height: 25mm !important;
        }

        .qr-id {
            font-size: 7px;
            color: #666;
            margin-top: 1mm;
            font-family: monospace;
        }

        /* Botão de impressão (não aparece na impressão) */
        .print-controls {
            text-align: center;
            padding: 15px;
            background: #f5f5f5;
            margin-bottom: 10px;
        }
        .print-controls button {
            padding: 8px 20px;
            background: #4a5d23;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
        }
        .print-controls button:hover { background: #3d4e1c; }
        .print-controls a {
            margin-left: 10px;
            color: #666;
            text-decoration: underline;
            font-size: 12px;
        }

        @media print {
            .print-controls { display: none; }
        }
    </style>
</head>
<body>

<div class="print-controls">
    <button onclick="window.print()">🖨️ Imprimir Etiqueta</button>
    <a href="{{ route('stock.show', $stockItem) }}">Voltar ao Item</a>

    @if(isset($multipleItems) && $multipleItems)
        <span style="margin-left: 10px; font-size: 11px; color: #666;">
            {{ count($items ?? []) }} etiquetas
        </span>
    @endif
</div>

@if(isset($items) && is_iterable($items))
    {{-- Múltiplas etiquetas --}}
    @foreach($items as $item)
        @include('stock.partials.label-single', ['stockItem' => $item, 'qrCode' => $qrCodes[$item->id] ?? ''])
    @endforeach
@else
    {{-- Etiqueta única --}}
    @include('stock.partials.label-single', ['stockItem' => $stockItem, 'qrCode' => $qrCode])
@endif

</body>
</html>
