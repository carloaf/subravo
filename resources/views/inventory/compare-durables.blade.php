@extends('layouts.app')

@section('title', 'Comparar com Uso Duradouro — SUBRAVO')
@section('page-title', 'Inventário × Uso Duradouro')

@section('header-actions')
    <div class="flex gap-2">
        <x-btn variant="outline" href="{{ route('inventory.show', $inventory) }}" size="sm">← Voltar ao Inventário</x-btn>
        <x-btn variant="secondary" href="{{ route('durables.index') }}" size="sm">Ver Uso Duradouro</x-btn>
    </div>
@endsection

@section('content')

{{-- Header --}}
<div class="mb-6 bg-gradient-to-r from-purple-600 to-indigo-700 rounded-xl shadow-lg p-6 text-white">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <svg class="w-8 h-8 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <h2 class="text-xl font-bold">Comparação com Uso Duradouro</h2>
            </div>
            <p class="text-purple-200 text-sm">
                Inventário: <span class="font-semibold text-white">{{ $inventory->filename }}</span>
            </p>
            <p class="text-purple-300 text-xs mt-0.5">
                {{ $inventory->header_display }} — {{ $inventory->created_at->format('d/m/Y H:i') }}
            </p>
        </div>
        <div class="text-right">
            <p class="text-purple-200 text-xs">Materiais Duradouros no PDF</p>
            <p class="text-2xl font-bold">{{ $durableItems->count() }}</p>
        </div>
    </div>
</div>

@if($durableItems->isEmpty())
    <x-empty-state
        title="Sem materiais de uso duradouro"
        message="Este inventário não contém itens classificados como 'Material de Uso Duradouro'."
    />
@else

{{-- Resumo --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    <x-stat-card title="Correspondentes"
                 :value="$summary['matched_count']"
                 icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                 color="green" />
    <x-stat-card title="Só no Inventário"
                 :value="$summary['only_inv_count']"
                 icon="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"
                 color="yellow" />
    <x-stat-card title="Só no Sistema"
                 :value="$summary['only_sys_count']"
                 icon="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"
                 color="orange" />
    <x-stat-card title="Qtd Inventário"
                 :value="$summary['inv_total_qty']"
                 icon="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"
                 color="blue" />
    <x-stat-card title="Qtd Sistema"
                 :value="$summary['sys_total_qty']"
                 icon="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"
                 color="purple" />
    <x-stat-card title="Valor Inventário"
                 :value="'R$ ' . number_format($summary['inv_total_value'], 2, ',', '.')"
                 icon="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                 color="yellow" />
</div>

{{-- Legenda --}}
<div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-3">
    <p class="text-xs text-blue-700">
        <strong>Correspondência:</strong> A comparação é feita pelo <span class="font-mono">Código do Material (SISCOFIS)</span> do inventário PDF
        com o campo <span class="font-mono">Código SISCOFIS</span> dos produtos cadastrados no sistema.
        Produtos sem código SISCOFIS definido não serão cruzados automaticamente.
    </p>
</div>

{{-- ═══ CORRESPONDENTES (match entre inventário e sistema) ═══ --}}
@if($matched->isNotEmpty())
<x-card class="mb-6">
    <div class="px-4 py-3 bg-green-50 border-b border-green-200 flex items-center justify-between">
        <h3 class="text-sm font-bold text-green-800 uppercase flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Materiais Correspondentes
        </h3>
        <span class="text-xs font-medium text-green-600 bg-green-100 px-2 py-0.5 rounded-full">
            {{ $matched->count() }} {{ $matched->count() === 1 ? 'item' : 'itens' }}
        </span>
    </div>

    <x-table>
        <x-slot:header>
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Material (Inventário)</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Produto (Sistema)</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Cód SISCOFIS</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Qtd Inv.</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Qtd Sistema</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Diferença</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Disponível</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Cautelado</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Danificado</th>
            </tr>
        </x-slot:header>

        @foreach($matched as $entry)
            @php
                $diffColor = $entry['qty_diff'] > 0 ? 'text-yellow-600' : ($entry['qty_diff'] < 0 ? 'text-red-600' : 'text-green-600');
                $diffBg = $entry['qty_diff'] === 0 ? '' : ($entry['qty_diff'] > 0 ? 'bg-yellow-50' : 'bg-red-50');
                $borderColor = $entry['qty_diff'] === 0 ? 'border-green-400' : ($entry['qty_diff'] > 0 ? 'border-yellow-400' : 'border-red-400');
            @endphp
            <tr class="hover:bg-gray-50 transition-colors border-l-4 {{ $borderColor }} {{ $diffBg }}">
                <td class="px-4 py-3">
                    <div class="text-sm font-medium text-gray-900">{{ $entry['inventory_item']->material_name }}</div>
                    <div class="text-xs text-gray-500">Vlr: {{ $entry['inventory_item']->formatted_total_value }}</div>
                </td>
                <td class="px-4 py-3">
                    <div class="text-sm font-medium text-gray-900">{{ $entry['product']->name }}</div>
                    <div class="text-xs text-gray-500">{{ $entry['product']->category->name ?? '—' }}</div>
                </td>
                <td class="px-4 py-3 text-center text-sm text-gray-700 font-mono">{{ $entry['product']->siscofis_code }}</td>
                <td class="px-4 py-3 text-center text-sm font-semibold text-purple-700">{{ $entry['inventory_item']->quantity }}</td>
                <td class="px-4 py-3 text-center text-sm font-semibold text-blue-700">{{ $entry['system_total'] }}</td>
                <td class="px-4 py-3 text-center text-sm font-bold {{ $diffColor }}">
                    @if($entry['qty_diff'] === 0)
                        <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    @else
                        {{ $entry['qty_diff'] > 0 ? '+' : '' }}{{ $entry['qty_diff'] }}
                    @endif
                </td>
                <td class="px-4 py-3 text-center text-sm text-green-600">{{ $entry['system_available'] }}</td>
                <td class="px-4 py-3 text-center text-sm text-orange-600">{{ $entry['system_loaned'] }}</td>
                <td class="px-4 py-3 text-center text-sm text-red-600">{{ $entry['system_damaged'] }}</td>
            </tr>
        @endforeach
    </x-table>
</x-card>
@endif

{{-- ═══ SÓ NO INVENTÁRIO PDF ═══ --}}
@if($onlyInventory->isNotEmpty())
<x-card class="mb-6">
    <div class="px-4 py-3 bg-yellow-50 border-b border-yellow-200 flex items-center justify-between">
        <h3 class="text-sm font-bold text-yellow-800 uppercase flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            Apenas no Inventário PDF (sem correspondência no sistema)
        </h3>
        <span class="text-xs font-medium text-yellow-600 bg-yellow-100 px-2 py-0.5 rounded-full">
            {{ $onlyInventory->count() }} {{ $onlyInventory->count() === 1 ? 'item' : 'itens' }}
        </span>
    </div>

    <x-table>
        <x-slot:header>
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Material</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Cód Mat</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Conta Contábil</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Qtd</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Vlr Unit</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Vlr Total</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Patrimônios</th>
            </tr>
        </x-slot:header>

        @foreach($onlyInventory as $item)
            <tr class="hover:bg-yellow-50/50 transition-colors border-l-4 border-yellow-400" x-data="{ showPat: false }">
                <td class="px-4 py-3">
                    <div class="text-sm font-medium text-gray-900">{{ $item->material_name }}</div>
                    @if($item->hasPatrimonyNumbers())
                        <button @click="showPat = !showPat"
                                class="mt-1 text-xs text-yellow-600 hover:text-yellow-800 font-medium flex items-center gap-1">
                            <svg class="w-3 h-3 transition-transform" :class="showPat ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            {{ $item->patrimony_count }} patrimônio(s)
                        </button>
                        <div x-show="showPat" x-collapse class="mt-2 text-xs text-gray-500 bg-gray-50 rounded p-2">
                            @foreach($item->patrimony_numbers as $pn)
                                <span class="inline-block bg-white border border-gray-200 rounded px-2 py-0.5 mr-1 mb-1 font-mono">{{ $pn }}</span>
                            @endforeach
                        </div>
                    @endif
                </td>
                <td class="px-4 py-3 text-center text-sm text-gray-700 font-mono">{{ $item->material_code }}</td>
                <td class="px-4 py-3 text-center text-sm text-gray-700 font-mono">{{ $item->accounting_account }}</td>
                <td class="px-4 py-3 text-center text-sm font-semibold text-gray-900">{{ $item->quantity }}</td>
                <td class="px-4 py-3 text-right text-sm text-gray-700">{{ $item->formatted_unit_value }}</td>
                <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">{{ $item->formatted_total_value }}</td>
                <td class="px-4 py-3 text-center text-sm text-gray-500">{{ $item->patrimony_count }}</td>
            </tr>
        @endforeach
    </x-table>

    <div class="px-4 py-3 bg-yellow-50 border-t border-yellow-200 text-xs text-yellow-700">
        <strong>Dica:</strong> Estes materiais constam no inventário PDF mas não possuem correspondência no sistema.
        Verifique se os produtos estão cadastrados com o código SISCOFIS correto.
    </div>
</x-card>
@endif

{{-- ═══ SÓ NO SISTEMA ═══ --}}
@if($onlySystem->isNotEmpty())
<x-card class="mb-6">
    <div class="px-4 py-3 bg-orange-50 border-b border-orange-200 flex items-center justify-between">
        <h3 class="text-sm font-bold text-orange-800 uppercase flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            Apenas no Sistema (sem correspondência no inventário PDF)
        </h3>
        <span class="text-xs font-medium text-orange-600 bg-orange-100 px-2 py-0.5 rounded-full">
            {{ $onlySystem->count() }} {{ $onlySystem->count() === 1 ? 'produto' : 'produtos' }}
        </span>
    </div>

    <x-table>
        <x-slot:header>
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Produto</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Categoria</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Cód SISCOFIS</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Total</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Disponível</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Cautelado</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Danificado</th>
            </tr>
        </x-slot:header>

        @foreach($onlySystem as $entry)
            <tr class="hover:bg-orange-50/50 transition-colors border-l-4 border-orange-400">
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $entry['product']->name }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $entry['product']->category->name ?? '—' }}</td>
                <td class="px-4 py-3 text-center text-sm text-gray-700 font-mono">{{ $entry['product']->siscofis_code ?? '—' }}</td>
                <td class="px-4 py-3 text-center text-sm font-semibold text-gray-900">{{ $entry['system_total'] }}</td>
                <td class="px-4 py-3 text-center text-sm text-green-600">{{ $entry['system_available'] }}</td>
                <td class="px-4 py-3 text-center text-sm text-orange-600">{{ $entry['system_loaned'] }}</td>
                <td class="px-4 py-3 text-center text-sm text-red-600">{{ $entry['system_damaged'] }}</td>
            </tr>
        @endforeach
    </x-table>

    <div class="px-4 py-3 bg-orange-50 border-t border-orange-200 text-xs text-orange-700">
        <strong>Atenção:</strong> Estes produtos duráveis existem no sistema mas não foram encontrados no inventário PDF.
        Pode indicar que o material não consta mais na carga da dependência.
    </div>
</x-card>
@endif

@endif {{-- durableItems isNotEmpty --}}

{{-- Total Geral --}}
<div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <span class="text-sm font-bold text-purple-800">RESUMO DA COMPARAÇÃO</span>
        <div class="flex flex-wrap items-center gap-6 text-sm">
            <div>
                <span class="text-purple-600">Correspondentes:</span>
                <span class="font-bold text-green-700">{{ $summary['matched_count'] }}</span>
            </div>
            <div>
                <span class="text-purple-600">Só Inventário:</span>
                <span class="font-bold text-yellow-700">{{ $summary['only_inv_count'] }}</span>
            </div>
            <div>
                <span class="text-purple-600">Só Sistema:</span>
                <span class="font-bold text-orange-700">{{ $summary['only_sys_count'] }}</span>
            </div>
            <div>
                <span class="text-purple-600">Qtd Inventário:</span>
                <span class="font-bold text-purple-900">{{ $summary['inv_total_qty'] }}</span>
            </div>
            <div>
                <span class="text-purple-600">Qtd Sistema:</span>
                <span class="font-bold text-purple-900">{{ $summary['sys_total_qty'] }}</span>
            </div>
        </div>
    </div>
</div>

@endsection
