@extends('layouts.app')

@section('title', $product->name . ' — SUBRAVO')
@section('page-title', $product->name)

@section('header-actions')
    <x-btn variant="outline" href="{{ route('products.edit', $product) }}" size="sm"
           icon="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">Editar</x-btn>
@endsection

@section('content')

@php
    $available = $product->getAvailableStock();
    $loaned    = $product->getLoanedStock();
    $belowMin  = $product->isBelowMinimum();
@endphp

{{-- Info do Produto --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2">
        <x-card title="Informações do Produto">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase">Categoria</p>
                    <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $product->category->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase">Unidade</p>
                    <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $product->unit }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase">Estoque Mínimo</p>
                    <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $product->minimum_stock }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase">Serializado</p>
                    <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $product->is_serialized ? 'Sim' : 'Não' }}</p>
                </div>
                {{-- Dados SISCOFIS --}}
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase">Código SISCOFIS</p>
                    <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $product->siscofis_code ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase">Validade</p>
                    <p class="text-sm font-semibold text-gray-900 mt-0.5">
                        @if($product->shelf_life_months)
                            {{ $product->shelf_life_months }} meses
                        @else
                            —
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase">Tipo de Material</p>
                    <p class="text-sm font-semibold text-gray-900 mt-0.5">
                        @if($product->is_durable)
                            <x-badge color="emerald">Uso Duradouro</x-badge>
                        @else
                            <x-badge color="gray">Consumível</x-badge>
                        @endif
                    </p>
                </div>
                @if($product->description)
                    <div class="sm:col-span-2">
                        <p class="text-xs text-gray-400 font-medium uppercase">Descrição</p>
                        <p class="text-sm text-gray-700 mt-0.5">{{ $product->description }}</p>
                    </div>
                @endif
            </div>
        </x-card>
    </div>

    <div class="space-y-4">
        <x-stat-card
            title="Disponível"
            value="{{ $available }}"
            icon="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
            color="{{ $belowMin ? 'red' : 'green' }}"
            subtitle="{{ $belowMin ? 'Abaixo do mínimo!' : 'Estoque normal' }}"
        />
        <x-stat-card
            title="Emprestado"
            value="{{ $loaned }}"
            icon="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            color="amber"
        />
    </div>
</div>

{{-- Itens de Estoque --}}
<x-card title="Itens de Estoque" subtitle="{{ $product->stockItems->count() }} lote(s)">
    <x-slot:actions>
        <x-btn variant="primary" href="{{ route('stock.entry') }}" size="sm"
               icon="M12 4v16m8-8H4">Entrada</x-btn>
    </x-slot:actions>

    @if($product->stockItems->isEmpty())
        <x-empty-state
            title="Nenhum item em estoque"
            message="Registre a primeira entrada deste produto."
            action="{{ route('stock.entry') }}"
            actionLabel="Nova Entrada"
        />
    @else
        <x-table>
            <x-slot:header>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Lote / Série</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Qtd</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Localização</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Entrada SISCOFIS</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Validade</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Ações</th>
                </tr>
            </x-slot:header>

            @foreach($product->stockItems as $item)
                @php
                    $statusColor = match($item->status) {
                        'available'      => 'green',
                        'loaned'         => 'blue',
                        'damaged'        => 'red',
                        'decommissioned' => 'gray',
                        default          => 'gray',
                    };
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <p class="text-sm font-medium text-gray-900">{{ $item->batch ?? '—' }}</p>
                        @if($item->serial_number)
                            <p class="text-xs text-gray-400">Nº Série: {{ $item->serial_number }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-sm font-semibold text-gray-900">{{ $item->quantity }}</td>
                    <td class="px-4 py-3 text-center">
                        <x-badge :color="$statusColor">{{ $item->getStatusLabel() }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $item->location ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if($item->siscofis_entry_date)
                            <span class="text-sm text-gray-700">{{ \Carbon\Carbon::parse($item->siscofis_entry_date)->format('d/m/Y') }}</span>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($item->expiration_date)
                            <x-badge color="{{ $item->isExpired() ? 'red' : ($item->isExpiringSoon() ? 'amber' : 'gray') }}">
                                {{ $item->expiration_date->format('d/m/Y') }}
                            </x-badge>
                        @else
                            <span class="text-xs text-gray-400">N/A</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('stock.show', $item) }}" class="p-1.5 text-gray-400 hover:text-emerald-600 rounded" title="Detalhes">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                    </td>
                </tr>
            @endforeach
        </x-table>
    @endif
</x-card>

@endsection
