@extends('layouts.app')

@section('title', 'Resultado da Comparação — SMARTSUB')
@section('page-title', 'Comparação de Inventários')

@section('header-actions')
    <div class="flex gap-2">
        <x-btn variant="outline" href="{{ route('inventory.compare', ['old' => $oldUpload->id, 'new' => $newUpload->id]) }}" size="sm">
            ← Nova Comparação
        </x-btn>
        <x-btn variant="secondary" href="{{ route('inventory.index') }}" size="sm">Inventários</x-btn>
    </div>
@endsection

@section('content')

{{-- Header com os dois inventários --}}
<div class="mb-6 grid grid-cols-1 lg:grid-cols-2 gap-4">
    {{-- Anterior --}}
    <div class="bg-orange-50 border border-orange-200 rounded-xl p-4">
        <div class="flex items-center gap-2 mb-2">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-orange-200 text-orange-700 text-xs font-bold">A</span>
            <h3 class="text-sm font-bold text-orange-800">Inventário Anterior (Base)</h3>
        </div>
        <p class="text-sm font-medium text-gray-900">{{ $oldUpload->filename }}</p>
        <p class="text-xs text-gray-600 mt-0.5">{{ $oldUpload->header_display }}</p>
        <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
            <span>{{ $oldUpload->created_at->format('d/m/Y H:i') }}</span>
            <span>{{ $oldUpload->total_items }} itens</span>
            <span>R$ {{ number_format($oldUpload->total_value, 2, ',', '.') }}</span>
        </div>
    </div>

    {{-- Recente --}}
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
        <div class="flex items-center gap-2 mb-2">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-200 text-emerald-700 text-xs font-bold">B</span>
            <h3 class="text-sm font-bold text-emerald-800">Inventário Recente</h3>
        </div>
        <p class="text-sm font-medium text-gray-900">{{ $newUpload->filename }}</p>
        <p class="text-xs text-gray-600 mt-0.5">{{ $newUpload->header_display }}</p>
        <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
            <span>{{ $newUpload->created_at->format('d/m/Y H:i') }}</span>
            <span>{{ $newUpload->total_items }} itens</span>
            <span>R$ {{ number_format($newUpload->total_value, 2, ',', '.') }}</span>
        </div>
    </div>
</div>

{{-- Resumo da comparação --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <x-stat-card title="Entradas"
                 :value="$summary['added_count']"
                 icon="M12 6v6m0 0v6m0-6h6m-6 0H6"
                 color="green" />
    <x-stat-card title="Saídas"
                 :value="$summary['removed_count']"
                 icon="M20 12H4"
                 color="red" />
    <x-stat-card title="Alterados"
                 :value="$summary['changed_count']"
                 icon="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                 color="yellow" />
    <x-stat-card title="Sem Alteração"
                 :value="$summary['unchanged_count']"
                 icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                 color="gray" />
</div>

{{-- Barra de resumo de valor --}}
<div class="mb-6 bg-white border border-gray-200 rounded-lg p-4 flex flex-wrap items-center justify-between gap-4">
    <div class="flex items-center gap-6 text-sm">
        <div>
            <span class="text-gray-500">Diferença Qtd:</span>
            <span class="font-bold {{ $summary['qty_diff'] > 0 ? 'text-green-600' : ($summary['qty_diff'] < 0 ? 'text-red-600' : 'text-gray-600') }}">
                {{ $summary['qty_diff'] > 0 ? '+' : '' }}{{ $summary['qty_diff'] }}
            </span>
        </div>
        <div>
            <span class="text-gray-500">Diferença Valor:</span>
            <span class="font-bold {{ $summary['value_diff'] > 0 ? 'text-green-600' : ($summary['value_diff'] < 0 ? 'text-red-600' : 'text-gray-600') }}">
                {{ $summary['value_diff'] > 0 ? '+' : '' }}R$ {{ number_format($summary['value_diff'], 2, ',', '.') }}
            </span>
        </div>
    </div>
    <div class="flex items-center gap-4 text-xs">
        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-green-400 inline-block"></span> Entrada</span>
        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-red-400 inline-block"></span> Saída</span>
        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-yellow-400 inline-block"></span> Alterado</span>
    </div>
</div>

{{-- ═══ ENTRADAS (novos itens) ═══ --}}
@if($added->isNotEmpty())
<x-card class="mb-6">
    <div class="px-4 py-3 bg-green-50 border-b border-green-200 flex items-center justify-between">
        <h3 class="text-sm font-bold text-green-800 uppercase flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Entradas (Novos Materiais)
        </h3>
        <span class="text-xs font-medium text-green-600 bg-green-100 px-2 py-0.5 rounded-full">
            {{ $added->count() }} {{ $added->count() === 1 ? 'item' : 'itens' }}
            — R$ {{ number_format($summary['added_value'], 2, ',', '.') }}
        </span>
    </div>

    <x-table>
        <x-slot:header>
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Material</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Tipo</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Cód Mat</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Qtd</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Vlr Total</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Patrimônios</th>
            </tr>
        </x-slot:header>

        @foreach($added as $entry)
            <tr class="hover:bg-green-50/50 transition-colors border-l-4 border-green-400">
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $entry['item']->material_name }}</td>
                <td class="px-4 py-3 text-center"><x-badge color="green">{{ Str::limit($entry['item']->material_type, 20) }}</x-badge></td>
                <td class="px-4 py-3 text-center text-sm text-gray-700 font-mono">{{ $entry['item']->material_code }}</td>
                <td class="px-4 py-3 text-center text-sm font-bold text-green-600">+{{ $entry['item']->quantity }}</td>
                <td class="px-4 py-3 text-right text-sm font-semibold text-green-600">+{{ $entry['item']->formatted_total_value }}</td>
                <td class="px-4 py-3 text-center text-sm text-gray-500">{{ $entry['item']->patrimony_count }}</td>
            </tr>
        @endforeach
    </x-table>
</x-card>
@endif

{{-- ═══ SAÍDAS (itens removidos) ═══ --}}
@if($removed->isNotEmpty())
<x-card class="mb-6">
    <div class="px-4 py-3 bg-red-50 border-b border-red-200 flex items-center justify-between">
        <h3 class="text-sm font-bold text-red-800 uppercase flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
            </svg>
            Saídas (Materiais Removidos)
        </h3>
        <span class="text-xs font-medium text-red-600 bg-red-100 px-2 py-0.5 rounded-full">
            {{ $removed->count() }} {{ $removed->count() === 1 ? 'item' : 'itens' }}
            — R$ {{ number_format($summary['removed_value'], 2, ',', '.') }}
        </span>
    </div>

    <x-table>
        <x-slot:header>
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Material</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Tipo</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Cód Mat</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Qtd</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Vlr Total</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Patrimônios</th>
            </tr>
        </x-slot:header>

        @foreach($removed as $entry)
            <tr class="hover:bg-red-50/50 transition-colors border-l-4 border-red-400">
                <td class="px-4 py-3 text-sm font-medium text-gray-900 line-through opacity-70">{{ $entry['item']->material_name }}</td>
                <td class="px-4 py-3 text-center"><x-badge color="red">{{ Str::limit($entry['item']->material_type, 20) }}</x-badge></td>
                <td class="px-4 py-3 text-center text-sm text-gray-700 font-mono">{{ $entry['item']->material_code }}</td>
                <td class="px-4 py-3 text-center text-sm font-bold text-red-600">-{{ $entry['item']->quantity }}</td>
                <td class="px-4 py-3 text-right text-sm font-semibold text-red-600">-{{ $entry['item']->formatted_total_value }}</td>
                <td class="px-4 py-3 text-center text-sm text-gray-500">{{ $entry['item']->patrimony_count }}</td>
            </tr>
        @endforeach
    </x-table>
</x-card>
@endif

{{-- ═══ ALTERADOS (mudanças em qtd/valor/patrimônio) ═══ --}}
@if($changed->isNotEmpty())
<x-card class="mb-6">
    <div class="px-4 py-3 bg-yellow-50 border-b border-yellow-200 flex items-center justify-between">
        <h3 class="text-sm font-bold text-yellow-800 uppercase flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Materiais Alterados
        </h3>
        <span class="text-xs font-medium text-yellow-600 bg-yellow-100 px-2 py-0.5 rounded-full">
            {{ $changed->count() }} {{ $changed->count() === 1 ? 'item' : 'itens' }}
        </span>
    </div>

    <x-table>
        <x-slot:header>
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Material</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Cód Mat</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Qtd Ant</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Qtd Nova</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Dif Qtd</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Vlr Ant</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Vlr Novo</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Dif Valor</th>
            </tr>
        </x-slot:header>

        @foreach($changed as $entry)
            @php
                $qtyColor = $entry['qty_diff'] > 0 ? 'text-green-600' : ($entry['qty_diff'] < 0 ? 'text-red-600' : 'text-gray-500');
                $valColor = $entry['value_diff'] > 0 ? 'text-green-600' : ($entry['value_diff'] < 0 ? 'text-red-600' : 'text-gray-500');
            @endphp
            <tr class="hover:bg-yellow-50/50 transition-colors border-l-4 border-yellow-400" x-data="{ showDetails: false }">
                <td class="px-4 py-3">
                    <div class="text-sm font-medium text-gray-900">{{ $entry['new']->material_name }}</div>

                    {{-- Patrimônios alterados --}}
                    @if($entry['added_patrimonies']->isNotEmpty() || $entry['removed_patrimonies']->isNotEmpty())
                        <button @click="showDetails = !showDetails"
                                class="mt-1 text-xs text-yellow-600 hover:text-yellow-800 font-medium flex items-center gap-1 transition-colors">
                            <svg class="w-3 h-3 transition-transform" :class="showDetails ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            Patrimônios alterados
                        </button>
                        <div x-show="showDetails" x-collapse class="mt-2 text-xs space-y-1">
                            @if($entry['added_patrimonies']->isNotEmpty())
                                <div>
                                    <span class="font-medium text-green-700">Novos:</span>
                                    @foreach($entry['added_patrimonies'] as $pn)
                                        <span class="inline-block bg-green-100 border border-green-200 rounded px-2 py-0.5 mr-1 mb-1 font-mono text-green-800">{{ $pn }}</span>
                                    @endforeach
                                </div>
                            @endif
                            @if($entry['removed_patrimonies']->isNotEmpty())
                                <div>
                                    <span class="font-medium text-red-700">Removidos:</span>
                                    @foreach($entry['removed_patrimonies'] as $pn)
                                        <span class="inline-block bg-red-100 border border-red-200 rounded px-2 py-0.5 mr-1 mb-1 font-mono text-red-800 line-through">{{ $pn }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </td>
                <td class="px-4 py-3 text-center text-sm text-gray-700 font-mono">{{ $entry['new']->material_code }}</td>
                <td class="px-4 py-3 text-center text-sm text-gray-500">{{ $entry['old']->quantity }}</td>
                <td class="px-4 py-3 text-center text-sm font-semibold text-gray-900">{{ $entry['new']->quantity }}</td>
                <td class="px-4 py-3 text-center text-sm font-bold {{ $qtyColor }}">
                    {{ $entry['qty_diff'] > 0 ? '+' : '' }}{{ $entry['qty_diff'] }}
                </td>
                <td class="px-4 py-3 text-right text-sm text-gray-500">{{ $entry['old']->formatted_total_value }}</td>
                <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">{{ $entry['new']->formatted_total_value }}</td>
                <td class="px-4 py-3 text-right text-sm font-bold {{ $valColor }}">
                    {{ $entry['value_diff'] > 0 ? '+' : '' }}R$ {{ number_format($entry['value_diff'], 2, ',', '.') }}
                </td>
            </tr>
        @endforeach
    </x-table>
</x-card>
@endif

{{-- ═══ SEM ALTERAÇÃO ═══ --}}
@if($unchanged->isNotEmpty())
<div x-data="{ showUnchanged: false }" class="mb-6">
    <button @click="showUnchanged = !showUnchanged"
            class="w-full text-left bg-gray-50 border border-gray-200 rounded-lg p-3 hover:bg-gray-100 transition-colors flex items-center justify-between">
        <span class="text-sm font-medium text-gray-600 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ $unchanged->count() }} {{ $unchanged->count() === 1 ? 'material sem alteração' : 'materiais sem alteração' }}
        </span>
        <svg class="w-4 h-4 text-gray-400 transition-transform" :class="showUnchanged ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div x-show="showUnchanged" x-collapse>
        <x-card class="mt-2">
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Material</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Cód Mat</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Qtd</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Vlr Total</th>
                    </tr>
                </x-slot:header>

                @foreach($unchanged as $entry)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $entry['new']->material_name }}</td>
                        <td class="px-4 py-3 text-center text-sm text-gray-500 font-mono">{{ $entry['new']->material_code }}</td>
                        <td class="px-4 py-3 text-center text-sm text-gray-700">{{ $entry['new']->quantity }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-700">{{ $entry['new']->formatted_total_value }}</td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>
    </div>
</div>
@endif

{{-- Total Geral da Comparação --}}
<div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <span class="text-sm font-bold text-emerald-800">RESUMO DA COMPARAÇÃO</span>
        <div class="flex flex-wrap items-center gap-6 text-sm">
            <div>
                <span class="text-emerald-600">Entradas:</span>
                <span class="font-bold text-green-700">+{{ $summary['added_count'] }}</span>
            </div>
            <div>
                <span class="text-emerald-600">Saídas:</span>
                <span class="font-bold text-red-700">-{{ $summary['removed_count'] }}</span>
            </div>
            <div>
                <span class="text-emerald-600">Alterados:</span>
                <span class="font-bold text-yellow-700">{{ $summary['changed_count'] }}</span>
            </div>
            <div>
                <span class="text-emerald-600">Saldo Qtd:</span>
                <span class="font-bold {{ $summary['qty_diff'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                    {{ $summary['qty_diff'] > 0 ? '+' : '' }}{{ $summary['qty_diff'] }}
                </span>
            </div>
            <div>
                <span class="text-emerald-600">Saldo Valor:</span>
                <span class="font-bold {{ $summary['value_diff'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                    {{ $summary['value_diff'] > 0 ? '+' : '' }}R$ {{ number_format($summary['value_diff'], 2, ',', '.') }}
                </span>
            </div>
        </div>
    </div>
</div>

@endsection
