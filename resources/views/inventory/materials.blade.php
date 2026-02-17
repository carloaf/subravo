@extends('layouts.app')

@section('title', 'Busca de Materiais — SUBRAVO')
@section('page-title', 'Busca de Materiais')

@section('header-actions')
    <div class="flex gap-2">
        <x-btn variant="outline" href="{{ route('inventory.reports') }}" size="sm"
               icon="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
            Relatórios
        </x-btn>
        <x-btn variant="primary" href="{{ route('inventory.index') }}" size="sm"
               icon="M15 19l-7-7 7-7">
            Voltar
        </x-btn>
    </div>
@endsection

@section('content')

{{-- Estatísticas --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card
        title="Registros Encontrados"
        :value="number_format($stats['total_items'])"
        icon="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
        color="blue"
    />
    <x-stat-card
        title="Materiais Distintos"
        :value="number_format($stats['unique_materials'])"
        icon="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"
        color="purple"
    />
    <x-stat-card
        title="Quantidade Somada"
        :value="number_format($stats['total_quantity'])"
        icon="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
        color="green"
    />
    <x-stat-card
        title="Valor Total"
        :value="'R$ ' . number_format($stats['total_value'], 2, ',', '.')"
        icon="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
        color="yellow"
    />
</div>

{{-- Alerta: Itens Ocultos --}}
@if(session('hidden_inventory_items') && count(session('hidden_inventory_items')) > 0)
    <div class="mb-6 bg-amber-50 border-l-4 border-amber-500 rounded-r-lg p-4">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
                <div class="text-sm text-amber-800">
                    <span class="font-semibold">{{ count(session('hidden_inventory_items')) }} item(ns) oculto(s)</span> — 
                    Você removeu alguns itens da visualização atual. Faça uma nova busca ou clique em "Mostrar Todos" para vê-los novamente.
                </div>
            </div>
            <form method="POST" action="{{ route('inventory.materials.reset-hidden') }}" class="flex-shrink-0">
                @csrf
                <button type="submit" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Mostrar Todos
                </button>
            </form>
        </div>
    </div>
@endif

{{-- Filtros --}}
<x-card class="mb-6">
    <form method="GET" action="{{ route('inventory.materials') }}" class="space-y-4">
        {{-- Busca Principal --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Buscar por nome, código ou ficha do material (ex: beliche, armário, mesa)..."
                       autofocus
                       style="transition: all 0.3s ease; background: rgba(255, 255, 255, 0.9);"
                       class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 placeholder-gray-400">
                <p class="mt-1 text-xs text-gray-500">💡 Dica: busca inteligente com remoção automática de acentos e correção de erros de digitação</p>
            </div>
            <x-btn type="submit" variant="primary" size="md" icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z">
                Buscar
            </x-btn>
        </div>

        {{-- Filtros Avançados --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tipo de Material</label>
                <select name="material_type"
                        style="transition: all 0.3s ease;"
                        class="w-full px-3 py-2 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900">
                    <option value="">Todos</option>
                    @foreach($materialTypes as $type)
                        <option value="{{ $type }}" @selected(request('material_type') == $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Dependência</label>
                <select name="dependency"
                        style="transition: all 0.3s ease;"
                        class="w-full px-3 py-2 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900">
                    <option value="">Todas</option>
                    @foreach($dependencies as $dep)
                        <option value="{{ $dep }}" @selected(request('dependency') == $dep)>{{ $dep }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Ordenar por</label>
                <select name="order_by"
                        style="transition: all 0.3s ease;"
                        class="w-full px-3 py-2 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900">
                    <option value="material_name" @selected(request('order_by') == 'material_name')>Nome</option>
                    <option value="total_value" @selected(request('order_by') == 'total_value')>Valor</option>
                    <option value="quantity" @selected(request('order_by') == 'quantity')>Quantidade</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Direção</label>
                <select name="order_dir"
                        style="transition: all 0.3s ease;"
                        class="w-full px-3 py-2 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900">
                    <option value="asc" @selected(request('order_dir') == 'asc')>Crescente</option>
                    <option value="desc" @selected(request('order_dir') == 'desc')>Decrescente</option>
                </select>
            </div>
        </div>

        @if(request()->hasAny(['search', 'material_type', 'dependency', 'unit']))
            <div class="flex justify-end">
                <x-btn variant="secondary" href="{{ route('inventory.materials') }}" size="sm">
                    Limpar Filtros
                </x-btn>
            </div>
        @endif
    </form>
</x-card>

{{-- Resultados --}}
@if(request()->filled('search'))
    @php
        $filteredQuantity = $items->sum('quantity');
        $filteredValue = $items->sum('total_value');
    @endphp
    <div class="mb-4">
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-emerald-800">
                            Encontrados <strong>{{ $items->total() }}</strong> registro(s) para "<strong>{{ request('search') }}</strong>"
                        </p>
                        <p class="text-xs text-emerald-700 mt-1">
                            📦 Quantidade total somada: <strong>{{ number_format($filteredQuantity) }}</strong> unidade(s) | 
                            💰 Valor: <strong>R$ {{ number_format($filteredValue, 2, ',', '.') }}</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if($items->isEmpty())
    <x-empty-state
        title="Nenhum material encontrado"
        message="Tente ajustar os filtros de busca."
    />
@else
    <x-card>
        <x-table>
            <x-slot:header>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Material</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tipo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Localizaç</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase bg-blue-50">Qtd</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Vlr Unit.</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Vlr Total</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Patrimônios</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Ações</th>
                </tr>
            </x-slot:header>

            @foreach($items as $item)
                @php
                    // Função para destacar termos de busca
                    $highlightSearch = function($text, $search) {
                        if (!$search) return $text;
                        $words = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);
                        foreach ($words as $word) {
                            if (strlen($word) >= 3) {
                                $text = preg_replace('/(' . preg_quote($word, '/') . ')/i', '<mark class="bg-yellow-200 px-1 rounded">$1</mark>', $text);
                            }
                        }
                        return $text;
                    };
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-900 text-sm">
                            {!! request('search') ? $highlightSearch($item->material_name, request('search')) : $item->material_name !!}
                        </div>
                        @if($item->material_code || $item->ficha_number)
                            <div class="text-xs text-gray-500 space-x-2">
                                @if($item->material_code)
                                    <span>Cód: {{ $item->material_code }}</span>
                                @endif
                                @if($item->ficha_number)
                                    <span>Ficha: {{ $item->ficha_number }}</span>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        {{ $item->material_type ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        <div class="font-medium">{{ $item->upload->dependency }}</div>
                        <div class="text-xs text-gray-500">{{ $item->upload->unit }}</div>
                    </td>
                    <td class="px-4 py-3 text-center bg-blue-50">
                        <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-sm font-bold text-blue-900 bg-blue-100 border border-blue-200">
                            {{ $item->quantity }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right text-sm text-gray-700">
                        R$ {{ number_format($item->unit_value, 2, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-right text-sm font-bold text-gray-900">
                        R$ {{ number_format($item->total_value, 2, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-center text-sm text-gray-600">
                        @if($item->hasPatrimonyNumbers())
                            <x-badge color="blue">{{ $item->patrimony_count }}</x-badge>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('inventory.show', $item->upload) }}"
                               class="text-emerald-600 hover:text-emerald-800 text-sm font-medium transition-colors"
                               title="Ver inventário">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                            
                            <form method="POST" action="{{ route('inventory.materials.hide') }}" class="inline">
                                @csrf
                                <input type="hidden" name="item_id" value="{{ $item->id }}">
                                <button type="submit" 
                                        class="text-amber-600 hover:text-amber-800 transition-colors"
                                        title="Ocultar desta visualização"
                                        onclick="return confirm('Remover este item da visualização atual?\n\nVocê poderá vê-lo novamente fazendo uma nova busca ou clicando em Mostrar Todos.');">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>

        {{-- Totalizador --}}
        @if($items->isNotEmpty())
            @php
                $pageQuantity = $items->sum('quantity');
                $pageValue = $items->sum('total_value');
            @endphp
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-sm">
                    <div class="font-semibold text-gray-700">
                        📊 Totais desta página: 
                        <span class="text-emerald-600">{{ number_format($pageQuantity) }}</span> unidade(s) • 
                        <span class="text-emerald-600">R$ {{ number_format($pageValue, 2, ',', '.') }}</span>
                    </div>
                    <div class="text-gray-600">
                        Mostrando {{ $items->firstItem() ?? 0 }} a {{ $items->lastItem() ?? 0 }} de {{ $items->total() }} registros
                    </div>
                </div>
            </div>
        @endif

        <div class="px-4 py-3 border-t border-gray-100">
            {{ $items->links() }}
        </div>

        {{-- Botões de Exportação --}}
        <div class="px-4 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600">
                    <svg class="w-4 h-4 inline mr-1 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Exportar todos os <strong>{{ $items->total() }} registros</strong> encontrados
                </div>
                
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('inventory.materials.export') }}" class="inline">
                        @csrf
                        <input type="hidden" name="format" value="pdf">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="type" value="{{ request('type') }}">
                        <button type="submit" 
                                class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Exportar PDF
                        </button>
                    </form>

                    <form method="POST" action="{{ route('inventory.materials.export') }}" class="inline">
                        @csrf
                        <input type="hidden" name="format" value="excel">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="type" value="{{ request('type') }}">
                        <button type="submit" 
                                class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Exportar Excel
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </x-card>
@endif

@endsection
