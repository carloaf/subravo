@extends('layouts.app')

@section('title', 'Estoque — SMARTSUB')
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
                   style="transition: all 0.3s ease; background: rgba(255, 255, 255, 0.9);"
                   class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 placeholder-gray-400">
        </div>
        <div class="w-full sm:w-48">
            <select name="product_id"
                    style="transition: all 0.3s ease; background: rgba(255, 255, 255, 0.9);"
                    class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900">
                <option value="" class="text-gray-400">Todos os produtos</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" @selected(request('product_id') == $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-full sm:w-40">
            <select name="status"
                    style="transition: all 0.3s ease; background: rgba(255, 255, 255, 0.9);"
                    class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900">
                <option value="" class="text-gray-400">Todos os status</option>
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
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Preço Unit.</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
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

                    // Resumir nome do produto: base + Cor + Tamanho
                    $fullName  = $item->product->name;
                    $shortName = $fullName;
                    if (str_contains($fullName, '/') || preg_match('/Cor:|Tipo:|Tecido:|Tamanho:/i', $fullName)) {
                        $parts   = explode('/', $fullName, 2);
                        $base    = trim($parts[0]);
                        $rest    = $parts[1] ?? $fullName;
                        $cor     = '';
                        $tamanho = '';
                        if (preg_match('/Cor:\s*([^;]+)/i', $rest, $cm)) {
                            $cor = trim($cm[1]);
                        }
                        if (preg_match('/Tamanho:\s*([^;]+)/i', $rest, $tm)) {
                            $tamanho = trim($tm[1]);
                        }
                        $shortName = trim($base . ($cor ? ' ' . $cor : '') . ($tamanho ? ' ' . $tamanho : ''));
                    }
                @endphp
                <tr x-data="{ editing: false }" class="hover:bg-gray-50 transition">

                    {{-- Produto --}}
                    <td x-show="!editing" class="px-4 py-3 max-w-[180px]">
                        <a href="{{ route('products.show', $item->product) }}"
                           class="text-sm font-medium text-gray-900 hover:text-emerald-600 block"
                           title="{{ $fullName }}">{{ $shortName }}</a>
                        <p class="text-xs text-gray-400">{{ $item->product->category->name ?? '' }}</p>
                    </td>

                    {{-- Lote / Série --}}
                    <td x-show="!editing" class="px-4 py-3">
                        <p class="text-sm text-gray-900">{{ $item->batch ?? '—' }}</p>
                        @if($item->serial_number)
                            <p class="text-xs text-gray-400">Nº {{ $item->serial_number }}</p>
                        @endif
                    </td>

                    {{-- Qtd --}}
                    <td x-show="!editing" class="px-4 py-3 text-center">
                        <span class="text-sm font-semibold text-gray-900">{{ $item->quantity }}</span>
                    </td>

                    {{-- Preço Unit. --}}
                    <td x-show="!editing" class="px-4 py-3 text-right whitespace-nowrap">
                        @if($item->unit_cost)
                            <span class="text-sm font-semibold text-emerald-700">{{ $item->formatted_unit_cost }}</span>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td x-show="!editing" class="px-4 py-3 text-center">
                        <x-badge :color="$statusColor">{{ $item->getStatusLabel() }}</x-badge>
                    </td>

                    {{-- Validade --}}
                    <td x-show="!editing" class="px-4 py-3">
                        @if($item->expiration_date)
                            <div class="text-xs">
                                @if($item->siscofis_entry_date)
                                    <p class="text-gray-500 mb-0.5">
                                        Ent.: <span class="font-medium">{{ $item->siscofis_entry_date->format('d/m/Y') }}</span>
                                    </p>
                                @endif
                                <x-badge color="{{ $item->isExpired() ? 'red' : ($item->isExpiringSoon() ? 'amber' : 'gray') }}">
                                    {{ $item->expiration_date->format('d/m/Y') }}
                                </x-badge>
                                @php
                                    $daysRemaining = (int) now()->diffInDays($item->expiration_date, false);
                                @endphp
                                @if($daysRemaining < 0)
                                    <p class="text-red-600 font-semibold mt-0.5">Vencido há {{ abs($daysRemaining) }}d</p>
                                @elseif($daysRemaining <= 30)
                                    <p class="text-amber-600 font-semibold mt-0.5">{{ $daysRemaining }}d restantes</p>
                                @endif
                            </div>
                        @else
                            <span class="text-xs text-gray-400">N/A</span>
                        @endif
                    </td>

                    {{-- Ações --}}
                    <td x-show="!editing" class="px-4 py-3 text-right whitespace-nowrap">
                        <div class="flex items-center justify-end space-x-1">
                            <a href="{{ route('stock.show', $item) }}" class="p-1.5 text-gray-400 hover:text-emerald-600 rounded transition" title="Detalhes">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('stock.label', $item) }}" class="p-1.5 text-gray-400 hover:text-purple-600 rounded transition" title="Etiqueta QR">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                </svg>
                            </a>
                            <button @click="editing = true" class="p-1.5 text-gray-400 hover:text-emerald-600 rounded transition" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                        </div>
                    </td>

                    {{-- Modo Edição (cobre todas as colunas) --}}
                    <td x-show="editing" colspan="7" class="px-4 py-4 bg-emerald-50">
                            <form method="POST" action="{{ route('stock.updateItem', $item) }}">
                                @csrf
                                @method('PUT')
                                
                                <div class="mb-3">
                                    <div class="text-sm font-semibold text-gray-700 mb-2">
                                        Editando: <span class="text-emerald-700">{{ $item->product->name }}</span>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-6 gap-3 mb-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Lote</label>
                                        <input type="text" name="batch" value="{{ $item->batch }}"
                                               style="transition: all 0.3s ease; background: rgba(255, 255, 255, 0.9);"
                                               class="w-full px-3 py-1.5 text-sm rounded-lg border-2 border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Nº Série</label>
                                        <input type="text" name="serial_number" value="{{ $item->serial_number }}"
                                               style="transition: all 0.3s ease; background: rgba(255, 255, 255, 0.9);"
                                               class="w-full px-3 py-1.5 text-sm rounded-lg border-2 border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">📅 Data Entrada SISCOFIS</label>
                                        <input type="date" name="siscofis_entry_date" 
                                               value="{{ $item->siscofis_entry_date?->format('Y-m-d') }}"
                                               style="transition: all 0.3s ease; background: rgba(255, 255, 255, 0.9);"
                                               class="w-full px-3 py-1.5 text-sm rounded-lg border-2 border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Localização</label>
                                        <input type="text" name="location" value="{{ $item->location }}"
                                               style="transition: all 0.3s ease; background: rgba(255, 255, 255, 0.9);"
                                               class="w-full px-3 py-1.5 text-sm rounded-lg border-2 border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Observações</label>
                                        <input type="text" name="notes" value="{{ $item->notes }}"
                                               placeholder="Observações..."
                                               style="transition: all 0.3s ease; background: rgba(255, 255, 255, 0.9);"
                                               class="w-full px-3 py-1.5 text-sm rounded-lg border-2 border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none">
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between pt-2 border-t border-emerald-200">
                                    <div class="text-xs text-gray-600">
                                        <span class="font-medium">Status:</span> <x-badge :color="$statusColor" size="sm">{{ $item->getStatusLabel() }}</x-badge>
                                        <span class="ml-3 font-medium">Qtd:</span> {{ $item->quantity }}
                                        
                                        {{-- Informações de validade expandidas --}}
                                        <div class="mt-2 p-2 bg-white/50 rounded border border-emerald-100">
                                            @if($item->siscofis_entry_date)
                                                <div class="flex items-center space-x-4">
                                                    <span>
                                                        <span class="font-medium">📅 Entrada SISCOFIS:</span> 
                                                        <span class="text-gray-900">{{ $item->siscofis_entry_date->format('d/m/Y') }}</span>
                                                    </span>
                                                    @if($item->product->shelf_life_months)
                                                        <span>
                                                            <span class="font-medium">⏱️ Validade Produto:</span> 
                                                            <span class="text-gray-900">{{ $item->product->shelf_life_months }} meses</span>
                                                        </span>
                                                    @endif
                                                    @if($item->expiration_date)
                                                        <span>
                                                            <span class="font-medium">📆 Vence em:</span> 
                                                            <span class="{{ $item->isExpired() ? 'text-red-600 font-bold' : ($item->isExpiringSoon() ? 'text-amber-600 font-semibold' : 'text-emerald-600') }}">
                                                                {{ $item->expiration_date->format('d/m/Y') }}
                                                            </span>
                                                        </span>
                                                        @php
                                                            $daysRemaining = now()->diffInDays($item->expiration_date, false);
                                                            $daysRemaining = (int) $daysRemaining;
                                                        @endphp
                                                        @if($daysRemaining < 0)
                                                            <span class="text-red-600 font-bold">
                                                                ⚠️ Vencido há {{ abs($daysRemaining) }} dias
                                                            </span>
                                                        @elseif($daysRemaining <= 30)
                                                            <span class="text-amber-600 font-semibold">
                                                                ⚠️ {{ $daysRemaining }} dias restantes
                                                            </span>
                                                        @else
                                                            <span class="text-emerald-600">
                                                                ✓ {{ $daysRemaining }} dias restantes
                                                            </span>
                                                        @endif
                                                    @endif
                                                </div>
                                                @if($item->product->shelf_life_months)
                                                    <div class="mt-1 text-[10px] text-gray-500">
                                                        💡 Ao alterar a Data Entrada SISCOFIS, a validade será recalculada automaticamente (+{{ $item->product->shelf_life_months }} meses)
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-gray-500">Sem data de entrada SISCOFIS registrada</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button type="button" @click="editing = false"
                                                class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">
                                            Cancelar
                                        </button>
                                        <button type="submit"
                                                class="px-3 py-1.5 text-sm font-medium text-white bg-emerald-600 border border-transparent rounded-md hover:bg-emerald-700 transition">
                                            💾 Salvar Alterações
                                        </button>
                                    </div>
                                </div>
                            </form>
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
