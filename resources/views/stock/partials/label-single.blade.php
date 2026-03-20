<div class="label">
    <div class="label-left">
        <div class="product-name">{{ $stockItem->product?->name ?? '—' }}</div>

        @if($stockItem->serial_number)
            <div class="label-line">
                <span class="lbl">Nº Série:</span> {{ $stockItem->serial_number }}
            </div>
        @endif

        @if($stockItem->batch)
            <div class="label-line">
                <span class="lbl">Lote:</span> {{ $stockItem->batch }}
            </div>
        @endif

        @if($stockItem->expiration_date)
            <div class="label-line">
                <span class="lbl">Validade:</span> {{ $stockItem->expiration_date->format('m/Y') }}
            </div>
        @endif

        <div class="label-line">
            <span class="lbl">Qtd:</span> {{ $stockItem->quantity }} {{ $stockItem->product?->unit ?? '' }}
        </div>

        @if($stockItem->location)
            <div class="label-line">
                <span class="lbl">Local:</span> {{ $stockItem->location }}
            </div>
        @endif

        @if($stockItem->subunit)
            <div class="label-line">
                <span class="lbl">Subun.:</span> {{ $stockItem->subunit }}
            </div>
        @endif

        <div class="system-tag">HelpSub - Mat. Intendencia</div>
    </div>

    <div class="label-right">
        <div class="qr-code">
            {!! $qrCode !!}
        </div>
        <div class="qr-id">#{{ $stockItem->id }}</div>
    </div>
</div>
