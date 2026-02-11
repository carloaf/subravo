@extends('layouts.app')

@section('title', 'Uso Duradouro — SUBRAVO')
@section('page-title', 'Material de Uso Duradouro')

@section('content')

{{-- Info header --}}
<div class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-lg shadow-lg p-6 mb-6 text-white">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold mb-2">Controle de Material Permanente</h2>
            <p class="text-emerald-50 text-sm">Monitoramento de produtos duráveis com controle de validade e localização</p>
        </div>
        <div class="text-right">
            <div class="text-3xl font-bold">{{ $durableProducts->count() }}</div>
            <div class="text-emerald-100 text-sm">Produtos Cadastrados</div>
        </div>
    </div>
</div>

{{-- Quick stats --}}
@php
    $totalItems = $durableItems->sum('total');
    $totalAvailable = $durableItems->sum('available');
    $totalLoaned = $durableItems->sum('loaned');
    $totalDamaged = $durableItems->sum('damaged');
    $totalExpiring = $durableItems->sum('expiring_soon');
    $totalExpired = $durableItems->sum('expired');
@endphp

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-2xl font-bold text-gray-900">{{ $totalItems }}</div>
        <div class="text-xs text-gray-500 uppercase">Total Itens</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-2xl font-bold text-emerald-600">{{ $totalAvailable }}</div>
        <div class="text-xs text-gray-500 uppercase">Disponíveis</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-2xl font-bold text-blue-600">{{ $totalLoaned }}</div>
        <div class="text-xs text-gray-500 uppercase">Emprestados</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-2xl font-bold text-red-600">{{ $totalDamaged }}</div>
        <div class="text-xs text-gray-500 uppercase">Danificados</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-2xl font-bold text-amber-600">{{ $totalExpiring }}</div>
        <div class="text-xs text-gray-500 uppercase">Vencendo</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-2xl font-bold text-rose-600">{{ $totalExpired }}</div>
        <div class="text-xs text-gray-500 uppercase">Vencidos</div>
    </div>
</div>

{{-- Filtros e ordenação --}}
<x-card class="mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <div class="flex gap-2">
            <select name="sort" class="rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                <option value="name" @selected(request('sort', 'name') === 'name')>Nome</option>
                <option value="total" @selected(request('sort') === 'total')>Total</option>
                <option value="available" @selected(request('sort') === 'available')>Disponíveis</option>
                <option value="loaned" @selected(request('sort') === 'loaned')>Emprestados</option>
                <option value="expiring" @selected(request('sort') === 'expiring')>Validade</option>
            </select>
            <select name="dir" class="rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                <option value="asc" @selected(request('dir', 'asc') === 'asc')>⬆ Crescente</option>
                <option value="desc" @selected(request('dir') === 'desc')>⬇ Decrescente</option>
            </select>
        </div>
        <x-btn type="submit" variant="outline" size="sm">Ordenar</x-btn>
        @if(request()->hasAny(['sort', 'dir']))
            <x-btn variant="secondary" href="{{ route('durables.index') }}" size="sm">Limpar</x-btn>
        @endif
    </form>
</x-card>

{{-- Lista de produtos duráveis --}}
@if($durableItems->isEmpty())
    <x-empty-state
        title="Nenhum produto durável cadastrado"
        message="Os produtos marcados como 'Uso Duradouro' aparecerão aqui."
    />
@else
    <div class="space-y-4">
        @foreach($durableItems as $item)
            <x-card>
                {{-- Header do produto --}}
                <div class="flex items-start justify-between mb-4 pb-4 border-b">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-lg font-bold text-gray-900">{{ $item->product->name }}</h3>
                            <x-badge color="emerald">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                </svg>
                                Uso Duradouro
                            </x-badge>
                        </div>
                        <div class="flex items-center gap-4 text-sm text-gray-600">
                            <span><strong>Categoria:</strong> {{ $item->product->category->name }}</span>
                            <span><strong>Código SISCOFIS:</strong> {{ $item->product->siscofis_code ?? '—' }}</span>
                            @if($item->product->shelf_life_months)
                                <span><strong>Validade:</strong> {{ $item->product->shelf_life_months }} meses</span>
                            @else
                                <span class="text-emerald-600 font-semibold">✓ Sem validade</span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('stock.index', ['product_id' => $item->product->id]) }}"
                       class="text-emerald-600 hover:text-emerald-700 text-sm font-medium">
                        Ver itens →
                    </a>
                </div>

                {{-- Estatísticas --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-gray-900">{{ $item->total }}</div>
                        <div class="text-xs text-gray-500 uppercase">Total</div>
                    </div>
                    <div class="text-center p-3 bg-emerald-50 rounded-lg">
                        <div class="text-2xl font-bold text-emerald-600">{{ $item->available }}</div>
                        <div class="text-xs text-emerald-700 uppercase">Disponíveis</div>
                    </div>
                    <div class="text-center p-3 bg-blue-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">{{ $item->loaned }}</div>
                        <div class="text-xs text-blue-700 uppercase">Emprestados</div>
                    </div>
                    <div class="text-center p-3 bg-red-50 rounded-lg">
                        <div class="text-2xl font-bold text-red-600">{{ $item->damaged }}</div>
                        <div class="text-xs text-red-700 uppercase">Danificados</div>
                    </div>
                </div>

                {{-- Alertas de validade --}}
                @if($item->expired > 0 || $item->expiring_soon > 0)
                    <div class="mt-4 space-y-2">
                        @if($item->expired > 0)
                            <div class="flex items-center gap-2 p-3 bg-rose-50 border border-rose-200 rounded-lg">
                                <svg class="w-5 h-5 text-rose-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm font-medium text-rose-900">
                                    <strong>{{ $item->expired }}</strong> {{ $item->expired === 1 ? 'item vencido' : 'itens vencidos' }} — Descarregar imediatamente
                                </span>
                            </div>
                        @endif
                        @if($item->expiring_soon > 0)
                            <div class="flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                                <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm font-medium text-amber-900">
                                    <strong>{{ $item->expiring_soon }}</strong> {{ $item->expiring_soon === 1 ? 'item vence' : 'itens vencem' }} nos próximos 30 dias
                                </span>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Detalhamento dos itens (collapsible) --}}
                @if($item->items->isNotEmpty())
                    <details class="mt-4">
                        <summary class="cursor-pointer text-sm font-medium text-emerald-600 hover:text-emerald-700 select-none">
                            ▸ Ver detalhamento de {{ $item->items->count() }} {{ $item->items->count() === 1 ? 'item' : 'itens' }}
                        </summary>
                        <div class="mt-3 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Lote</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Série</th>
                                        <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600 uppercase">Qtd</th>
                                        <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Localização</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Entrada SISCOFIS</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Validade</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($item->items as $stockItem)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 text-sm text-gray-900">{{ $stockItem->batch ?? '—' }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-600">{{ $stockItem->serial_number ?? '—' }}</td>
                                            <td class="px-3 py-2 text-center text-sm font-semibold text-gray-900">{{ $stockItem->quantity }}</td>
                                            <td class="px-3 py-2 text-center">
                                                @php
                                                    $statusColor = match($stockItem->status) {
                                                        'available'      => 'green',
                                                        'loaned'         => 'blue',
                                                        'damaged'        => 'red',
                                                        'decommissioned' => 'gray',
                                                        default          => 'gray',
                                                    };
                                                @endphp
                                                <x-badge :color="$statusColor" size="sm">{{ $stockItem->getStatusLabel() }}</x-badge>
                                            </td>
                                            <td class="px-3 py-2 text-sm text-gray-600">{{ $stockItem->location ?? '—' }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-600">
                                                {{ $stockItem->siscofis_entry_date ? $stockItem->siscofis_entry_date->format('d/m/Y') : '—' }}
                                            </td>
                                            <td class="px-3 py-2">
                                                @if($stockItem->expiration_date)
                                                    <x-badge :color="$stockItem->isExpired() ? 'red' : ($stockItem->isExpiringSoon() ? 'amber' : 'gray')" size="sm">
                                                        {{ $stockItem->expiration_date->format('d/m/Y') }}
                                                    </x-badge>
                                                @else
                                                    <span class="text-xs text-gray-400">Sem validade</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                @endif
            </x-card>
        @endforeach
    </div>
@endif

@endsection
