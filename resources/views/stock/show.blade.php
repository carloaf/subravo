@extends('layouts.app')

@section('title', 'Item de Estoque — SMARTSUB')
@section('page-title', $stockItem->product->name)

@section('header-actions')
    <div class="flex items-center space-x-2">
        <x-btn variant="outline" href="{{ route('stock.label', $stockItem) }}" size="sm"
               icon="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">Etiqueta QR</x-btn>
        @if($stockItem->isAvailable())
            <x-btn variant="outline" href="{{ route('stock.adjust', $stockItem) }}" size="sm"
                   icon="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4">Ajustar</x-btn>
        @endif
    </div>
@endsection

@section('content')

@php
    $statusColor = match($stockItem->status) {
        'available'      => 'green',
        'loaned'         => 'blue',
        'damaged'        => 'red',
        'decommissioned' => 'gray',
        default          => 'gray',
    };
@endphp

{{-- Detalhes --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2">
        <x-card title="Detalhes do Item">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase">Produto</p>
                    <a href="{{ route('products.show', $stockItem->product) }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800 mt-0.5">
                        {{ $stockItem->product->name }}
                    </a>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase">Categoria</p>
                    <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $stockItem->product->category->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase">Lote</p>
                    <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $stockItem->batch ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase">Número de Série</p>
                    <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $stockItem->serial_number ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase">Localização</p>
                    <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $stockItem->location ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase">Subunidade</p>
                    <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $stockItem->subunit ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase">Data de Validade</p>
                    <p class="text-sm mt-0.5">
                        @if($stockItem->expiration_date)
                            <x-badge color="{{ $stockItem->isExpired() ? 'red' : ($stockItem->isExpiringSoon() ? 'amber' : 'green') }}">
                                {{ $stockItem->expiration_date->format('d/m/Y') }}
                            </x-badge>
                        @else
                            <span class="text-gray-500">N/A</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase">Entrada SISCOFIS</p>
                    <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $stockItem->siscofis_entry_date?->format('d/m/Y') ?? '—' }}</p>
                </div>
                @if($stockItem->notes)
                    <div class="sm:col-span-2">
                        <p class="text-xs text-gray-400 font-medium uppercase">Observações</p>
                        <p class="text-sm text-gray-700 mt-0.5">{{ $stockItem->notes }}</p>
                    </div>
                @endif
            </div>
        </x-card>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center">
            <p class="text-xs text-gray-400 font-medium uppercase mb-1">Quantidade</p>
            <p class="text-3xl font-bold text-gray-900">{{ $stockItem->quantity }}</p>
            <p class="text-sm text-gray-500 mt-1">{{ $stockItem->product->unit }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center">
            <p class="text-xs text-gray-400 font-medium uppercase mb-1">Status</p>
            <x-badge :color="$statusColor" class="text-sm px-3 py-1">{{ $stockItem->getStatusLabel() }}</x-badge>
        </div>
    </div>
</div>

{{-- Histórico de Movimentações --}}
<x-card title="Histórico de Movimentações" subtitle="Últimas 20 movimentações deste item">
    @if($stockItem->movements->isEmpty())
        <x-empty-state title="Nenhuma movimentação registrada" />
    @else
        <x-table>
            <x-slot:header>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Data</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Tipo</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Qtd</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Responsável</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Observações</th>
                </tr>
            </x-slot:header>

            @foreach($stockItem->movements as $move)
                @php
                    $typeColor = match($move->movement_type) {
                        'entry', 'return' => 'green',
                        'loan', 'exit'    => 'amber',
                        'adjustment'      => 'blue',
                        'decommission'    => 'red',
                        default           => 'gray',
                    };
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $move->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-center">
                        <x-badge :color="$typeColor">{{ $move->getTypeLabel() }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-center text-sm font-semibold {{ $move->quantity >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $move->quantity >= 0 ? '+' : '' }}{{ $move->quantity }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $move->performedBy?->getDisplayName() ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500 max-w-xs truncate">{{ $move->notes ?? '—' }}</td>
                </tr>
            @endforeach
        </x-table>
    @endif
</x-card>

@endsection
