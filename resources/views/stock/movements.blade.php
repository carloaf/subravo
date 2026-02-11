@extends('layouts.app')

@section('title', 'Movimentações — SUBRAVO')
@section('page-title', 'Movimentações de Estoque')

@section('content')

{{-- Filtros --}}
<x-card class="mb-6">
    <form method="GET" action="{{ route('stock.movements') }}" class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Buscar por produto..."
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
        </div>
        <div class="w-full sm:w-40">
            <select name="type"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                <option value="">Todos os tipos</option>
                @foreach($types as $val => $label)
                    <option value="{{ $val }}" @selected(request('type') == $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-full sm:w-40">
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm"
                   placeholder="De">
        </div>
        <div class="w-full sm:w-40">
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm"
                   placeholder="Até">
        </div>
        <x-btn type="submit" variant="outline" size="md" icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z">Filtrar</x-btn>
        @if(request()->hasAny(['search', 'type', 'date_from', 'date_to']))
            <x-btn variant="secondary" href="{{ route('stock.movements') }}" size="md">Limpar</x-btn>
        @endif
    </form>
</x-card>

{{-- Tabela --}}
@if($movements->isEmpty())
    <x-empty-state
        title="Nenhuma movimentação encontrada"
        message="As movimentações são registradas automaticamente ao realizar entradas, empréstimos e devoluções."
    />
@else
    <x-card>
        <x-table>
            <x-slot:header>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Data/Hora</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Tipo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Produto</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Qtd</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Responsável</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Observações</th>
                </tr>
            </x-slot:header>

            @foreach($movements as $move)
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
                    <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $move->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-center">
                        <x-badge :color="$typeColor">{{ $move->getTypeLabel() }}</x-badge>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-sm font-medium text-gray-900">{{ $move->stockItem?->product?->name ?? '—' }}</p>
                        @if($move->stockItem?->batch)
                            <p class="text-xs text-gray-400">Lote: {{ $move->stockItem->batch }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-sm font-semibold {{ $move->quantity >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $move->quantity >= 0 ? '+' : '' }}{{ $move->quantity }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $move->performedBy?->getDisplayName() ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500 max-w-xs truncate">{{ $move->notes ?? '—' }}</td>
                </tr>
            @endforeach
        </x-table>

        @if($movements->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $movements->links() }}
            </div>
        @endif
    </x-card>
@endif

@endsection
