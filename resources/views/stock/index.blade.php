@extends('layouts.app')

@section('title', 'Estoque — SUBRAVO')
@section('page-title', 'Itens de Estoque')

@section('header-actions')
    <x-btn variant="primary" href="{{ route('stock.entry') }}" size="sm"
           icon="M12 4v16m8-8H4">Nova Entrada</x-btn>
@endsection

@section('content')

{{-- Filtros --}}
<x-card class="mb-6">
    <form method="GET" action="{{ route('stock.index') }}" class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Buscar por produto, lote, série ou localização..."
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
        </div>
        <div class="w-full sm:w-48">
            <select name="product_id"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                <option value="">Todos os produtos</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" @selected(request('product_id') == $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-full sm:w-40">
            <select name="status"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                <option value="">Todos os status</option>
                @foreach($statuses as $val => $label)
                    <option value="{{ $val }}" @selected(request('status') == $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <x-btn type="submit" variant="outline" size="md" icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z">Buscar</x-btn>
        @if(request()->hasAny(['search', 'product_id', 'status']))
            <x-btn variant="secondary" href="{{ route('stock.index') }}" size="md">Limpar</x-btn>
        @endif
    </form>
</x-card>

{{-- Tabela --}}
@if($items->isEmpty())
    <x-empty-state
        title="Nenhum item em estoque"
        message="Registre a primeira entrada de material."
        action="{{ route('stock.entry') }}"
        actionLabel="Nova Entrada"
    />
@else
    <x-card>
        <x-table>
            <x-slot:header>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Produto</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Lote / Série</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Qtd</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Localização</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Validade</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Ações</th>
                </tr>
            </x-slot:header>

            @foreach($items as $item)
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
                        <a href="{{ route('products.show', $item->product) }}" class="text-sm font-medium text-gray-900 hover:text-emerald-600">
                            {{ $item->product->name }}
                        </a>
                        <p class="text-xs text-gray-400">{{ $item->product->category->name ?? '' }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-sm text-gray-900">{{ $item->batch ?? '—' }}</p>
                        @if($item->serial_number)
                            <p class="text-xs text-gray-400">Nº {{ $item->serial_number }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-sm font-semibold text-gray-900">{{ $item->quantity }}</td>
                    <td class="px-4 py-3 text-center">
                        <x-badge :color="$statusColor">{{ $item->getStatusLabel() }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $item->location ?? '—' }}</td>
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
                        <div class="flex items-center justify-end space-x-1">
                            <a href="{{ route('stock.show', $item) }}" class="p-1.5 text-gray-400 hover:text-emerald-600 rounded" title="Detalhes">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('stock.label', $item) }}" class="p-1.5 text-gray-400 hover:text-purple-600 rounded" title="Etiqueta QR">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                </svg>
                            </a>
                            @if($item->isAvailable())
                                <a href="{{ route('stock.adjust', $item) }}" class="p-1.5 text-gray-400 hover:text-amber-600 rounded" title="Ajuste">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>

        @if($items->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $items->links() }}
            </div>
        @endif
    </x-card>
@endif

@endsection
