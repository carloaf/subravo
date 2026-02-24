@extends('layouts.app')

@section('title', 'Inventário: {{ $inventory->filename }} — SMARTSUB')
@section('page-title', 'Detalhes do Inventário')

@section('header-actions')
    <div class="flex gap-2">
        <x-btn variant="outline" href="{{ route('inventory.edit-location', $inventory) }}" size="sm"
               icon="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z">Editar Localização</x-btn>
        <x-btn variant="outline" href="{{ route('inventory.compare.durables', $inventory) }}" size="sm"
               icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">Comparar c/ Duradouro</x-btn>
        <x-btn variant="outline" href="{{ route('inventory.download', $inventory) }}" size="sm"
               icon="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">Baixar PDF</x-btn>
        @if(auth()->user()->isAdmin())
            <form method="POST" action="{{ route('inventory.reprocess', $inventory) }}" class="inline">
                @csrf
                <x-btn type="submit" variant="secondary" size="sm"
                       icon="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                       onclick="return confirm('Reprocessar este inventário? Os itens atuais serão substituídos.')">
                    Reprocessar
                </x-btn>
            </form>
            <form method="POST" action="{{ route('inventory.sync-durables', $inventory) }}" class="inline">
                @csrf
                <x-btn type="submit" variant="primary" size="sm"
                       icon="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    Sync Duráveis
                </x-btn>
            </form>
        @endif
        <x-btn variant="secondary" href="{{ route('inventory.index') }}" size="sm">← Voltar</x-btn>
    </div>
@endsection

@section('content')

{{-- Header do inventário --}}
<div class="mb-6 bg-gradient-to-r from-emerald-600 to-green-700 rounded-xl shadow-lg p-6 text-white">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <svg class="w-8 h-8 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <h2 class="text-xl font-bold">{{ $inventory->filename }}</h2>
            </div>
            @if($inventory->dependency || $inventory->unit)
                <p class="text-emerald-100 text-sm">
                    {{ $inventory->dependency }}
                    @if($inventory->unit)
                        / {{ $inventory->unit }}
                    @endif
                    @if($inventory->unit_code)
                        — {{ $inventory->unit_code }}
                    @endif
                </p>
            @endif
            <p class="text-emerald-200 text-xs mt-1">
                Carregado por {{ $inventory->uploader->war_name ?? '—' }}
                em {{ $inventory->created_at->format('d/m/Y \à\s H:i') }}
            </p>
        </div>
        <div>
            @php
                $statusColor = match($inventory->status) {
                    'completed'  => 'bg-green-500',
                    'processing' => 'bg-blue-500',
                    'pending'    => 'bg-yellow-500',
                    'error'      => 'bg-red-500',
                    default      => 'bg-gray-500',
                };
            @endphp
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $statusColor }} text-white">
                {{ $inventory->status_label }}
            </span>
        </div>
    </div>
</div>

{{-- Erro (se houver) --}}
@if($inventory->hasError() && $inventory->error_message)
    <div class="mb-6 bg-red-50 border border-red-300 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <div>
                <h4 class="text-sm font-semibold text-red-800">Erro no Processamento</h4>
                <p class="text-sm text-red-700 mt-1">{{ $inventory->error_message }}</p>
            </div>
        </div>
    </div>
@endif

<<<<<<< HEAD
=======
{{-- Alerta: Localização não definida --}}
@if(!$inventory->dependency && !$inventory->unit)
    <div class="mb-6 bg-amber-50 border-l-4 border-amber-500 rounded-r-lg p-4">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-.834-1.964-.834-2.732 0L3.082 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <h4 class="text-sm font-semibold text-amber-800">Localização não identificada</h4>
                    <p class="text-sm text-amber-700 mt-1">
                        Este inventário não possui informações de dependência e unidade. 
                        A extração automática falhou ou o PDF não segue o formato padrão do SISCOFIS.
                    </p>
                </div>
            </div>
            <a href="{{ route('inventory.edit-location', $inventory) }}" 
               class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Definir Agora
            </a>
        </div>
    </div>
@endif

>>>>>>> dev
{{-- Estatísticas --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <x-stat-card title="Itens" :value="$stats['total_items']"
                 icon="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                 color="emerald" />
    <x-stat-card title="Quantidade Total" :value="$stats['total_quantity']"
                 icon="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"
                 color="blue" />
    <x-stat-card title="Valor Total" :value="'R$ ' . number_format($stats['total_value'], 2, ',', '.')"
                 icon="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                 color="yellow" />
    <x-stat-card title="Nr. Patrimoniais" :value="$stats['patrimony_count']"
                 icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                 color="purple" />
</div>

{{-- Itens agrupados por tipo --}}
@forelse($groupedItems as $type => $items)
    <x-card class="mb-6">
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-800 uppercase">
                <svg class="w-4 h-4 inline mr-1 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                {{ $type ?? 'Sem classificação' }}
            </h3>
            <span class="text-xs font-medium text-gray-500 bg-gray-200 px-2 py-0.5 rounded-full">
                {{ $items->count() }} {{ $items->count() === 1 ? 'item' : 'itens' }}
            </span>
        </div>

        <x-table>
            <x-slot:header>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Material</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Nr Ficha</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Cód Mat</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Conta Contábil</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Acervo</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Qtd</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Vlr Unit</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Vlr Total</th>
                </tr>
            </x-slot:header>

            @foreach($items as $item)
                <tr class="hover:bg-gray-50 transition-colors" x-data="{ showPatrimony: false }">
                    <td class="px-4 py-3">
                        <div class="text-sm font-medium text-gray-900">{{ $item->material_name }}</div>
                        @if($item->hasPatrimonyNumbers())
                            <button @click="showPatrimony = !showPatrimony"
                                    class="mt-1 text-xs text-emerald-600 hover:text-emerald-800 font-medium flex items-center gap-1 transition-colors">
                                <svg class="w-3 h-3 transition-transform" :class="showPatrimony ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                {{ $item->patrimony_count }} patrimônio(s)
                            </button>
                            <div x-show="showPatrimony" x-collapse class="mt-2 text-xs text-gray-500 bg-gray-50 rounded p-2">
                                @foreach($item->patrimony_numbers as $pn)
                                    <span class="inline-block bg-white border border-gray-200 rounded px-2 py-0.5 mr-1 mb-1 font-mono">{{ $pn }}</span>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-sm text-gray-700 font-mono">{{ $item->ficha_number }}</td>
                    <td class="px-4 py-3 text-center text-sm text-gray-700 font-mono">{{ $item->material_code }}</td>
                    <td class="px-4 py-3 text-center text-sm text-gray-700 font-mono">{{ $item->accounting_account }}</td>
                    <td class="px-4 py-3 text-center">
                        <x-badge :color="$item->acervo === 'S' ? 'green' : 'gray'">{{ $item->acervo }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-center text-sm font-semibold text-gray-900">{{ $item->quantity }}</td>
                    <td class="px-4 py-3 text-right text-sm text-gray-700">{{ $item->formatted_unit_value }}</td>
                    <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">{{ $item->formatted_total_value }}</td>
                </tr>
            @endforeach

            {{-- Subtotal por tipo --}}
            <tr class="bg-gray-50 border-t-2 border-gray-200">
                <td colspan="5" class="px-4 py-2 text-right text-xs font-bold text-gray-600 uppercase">Subtotal</td>
                <td class="px-4 py-2 text-center text-sm font-bold text-gray-900">{{ $items->sum('quantity') }}</td>
                <td class="px-4 py-2 text-right text-sm text-gray-700">—</td>
                <td class="px-4 py-2 text-right text-sm font-bold text-gray-900">
                    R$ {{ number_format($items->sum('total_value'), 2, ',', '.') }}
                </td>
            </tr>
        </x-table>
    </x-card>
@empty
    <x-empty-state
        title="Nenhum item encontrado"
        message="Este inventário não contém itens. Tente reprocessar o PDF."
    />
@endforelse

{{-- Total Geral --}}
@if($groupedItems->isNotEmpty())
    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 flex items-center justify-between">
        <span class="text-sm font-bold text-emerald-800">TOTAL GERAL DO INVENTÁRIO</span>
        <div class="flex items-center gap-6 text-sm">
            <div>
                <span class="text-emerald-600">Itens:</span>
                <span class="font-bold text-emerald-900">{{ $stats['total_items'] }}</span>
            </div>
            <div>
                <span class="text-emerald-600">Quantidade:</span>
                <span class="font-bold text-emerald-900">{{ $stats['total_quantity'] }}</span>
            </div>
            <div>
                <span class="text-emerald-600">Valor:</span>
                <span class="font-bold text-emerald-900">R$ {{ number_format($stats['total_value'], 2, ',', '.') }}</span>
            </div>
        </div>
    </div>
@endif

@endsection
