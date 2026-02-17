@extends('layouts.app')

@section('title', 'Inventário — SUBRAVO')
@section('page-title', 'Inventário SISCOFIS')

@section('header-actions')
    <div class="flex gap-2">
        <x-btn variant="outline" href="{{ route('inventory.materials') }}" size="sm"
               icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z">Buscar</x-btn>
        <x-btn variant="outline" href="{{ route('inventory.reports') }}" size="sm"
               icon="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">Relatórios</x-btn>
        <x-btn variant="outline" href="{{ route('inventory.compare') }}" size="sm"
               icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">Comparar</x-btn>
        <x-btn variant="primary" href="{{ route('inventory.create') }}" size="sm"
               icon="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12">Carregar PDF</x-btn>
    </div>
@endsection

@section('content')

{{-- Estatísticas --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card
        title="PDFs Carregados"
        :value="$stats['total_uploads']"
        icon="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
        color="emerald"
    />
    <x-stat-card
        title="Processados"
        :value="$stats['completed']"
        icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
        color="green"
    />
    <x-stat-card
        title="Total de Itens"
        :value="$stats['total_items']"
        icon="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
        color="blue"
    />
    <x-stat-card
        title="Valor Total"
        :value="'R$ ' . number_format($stats['total_value'], 2, ',', '.')"
        icon="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
        color="yellow"
    />
</div>

{{-- Filtros (apenas quando visualizar uploads de um mês específico) --}}
@if(isset($uploads))
    <x-card class="mb-6">
        <form method="GET" action="{{ route('inventory.index') }}" class="flex flex-col sm:flex-row gap-3">
            <input type="hidden" name="month" value="{{ request('month') }}">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Buscar por nome do arquivo, dependência ou unidade..."
                       style="transition: all 0.3s ease; background: rgba(255, 255, 255, 0.9);"
                       class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 placeholder-gray-400">
            </div>
            <div class="w-full sm:w-48">
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
            @if(request()->hasAny(['search', 'status']))
                <x-btn variant="secondary" href="{{ route('inventory.index', ['month' => request('month')]) }}" size="md">Limpar</x-btn>
            @endif
        </form>
    </x-card>
@endif

{{-- Visualização: Cards de Meses --}}
@if(isset($months))
    @if($months->isEmpty())
        <x-empty-state
            title="Nenhum inventário carregado"
            message="Carregue um PDF de Relação de Material Carga do SISCOFIS para começar."
            action="{{ route('inventory.create') }}"
            actionLabel="Carregar PDF"
        />
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($months as $month)
                <a href="{{ route('inventory.index', ['month' => $month['year_month']]) }}"
                   class="group block bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 overflow-hidden">
                    
                    {{-- Conteúdo do Card do Mês --}}
                    <div class="p-6 text-white">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        
                        <h3 class="text-2xl font-bold capitalize mb-2">{{ $month['label'] }}</h3>
                        
                        <div class="flex items-center gap-2">
                            <div class="bg-white/20 backdrop-blur-sm px-3 py-1.5 rounded-lg">
                                <span class="text-3xl font-bold">{{ $month['count'] }}</span>
                            </div>
                            <span class="text-white/90 font-medium">{{ $month['count'] === 1 ? 'upload' : 'uploads' }}</span>
                        </div>
                    </div>
                    
                    {{-- Footer com Ícone de Ação --}}
                    <div class="bg-black/10 backdrop-blur-sm px-6 py-3 flex items-center justify-end">
                        <span class="text-white text-sm font-medium flex items-center gap-2 group-hover:gap-3 transition-all">
                            Ver arquivos
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endif

{{-- Visualização: Uploads do Mês Selecionado --}}
@if(isset($uploads))
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('inventory.index') }}"
                   class="text-emerald-600 hover:text-emerald-800 transition-colors"
                   title="Voltar para meses">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h2 class="text-2xl font-bold text-gray-900 capitalize">{{ $selectedMonth }}</h2>
            </div>
            <span class="text-sm text-gray-500 font-medium">{{ $uploads->count() }} {{ $uploads->count() === 1 ? 'arquivo' : 'arquivos' }}</span>
        </div>
    </div>

    @if($uploads->isEmpty())
        <x-empty-state
            title="Nenhum arquivo encontrado"
            message="Não há uploads para este período com os filtros aplicados."
            action="{{ route('inventory.index') }}"
            actionLabel="Voltar"
        />
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($uploads as $upload)
                @php
                    $statusColor = match($upload->status) {
                        'completed'  => 'green',
                        'processing' => 'blue',
                        'pending'    => 'yellow',
                        'error'      => 'red',
                        default      => 'gray',
                    };
                    $borderColor = match($upload->status) {
                        'completed'  => 'border-green-200 hover:border-green-400',
                        'processing' => 'border-blue-200 hover:border-blue-400',
                        'pending'    => 'border-yellow-200 hover:border-yellow-400',
                        'error'      => 'border-red-200 hover:border-red-400',
                        default      => 'border-gray-200 hover:border-gray-400',
                    };
                @endphp
                <a href="{{ route('inventory.show', $upload) }}"
                   class="group relative block bg-white rounded-xl shadow-sm {{ $borderColor }} border-2 overflow-hidden transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    
                    {{-- Header do Card --}}
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span class="text-sm font-semibold text-gray-900 truncate group-hover:text-emerald-600 transition-colors" title="{{ $upload->filename }}">
                                    {{ $upload->filename }}
                                </span>
                            </div>
                            <x-badge :color="$statusColor" class="flex-shrink-0">{{ $upload->status_label }}</x-badge>
                        </div>
                    </div>

                    {{-- Corpo do Card --}}
                    <div class="px-4 py-4 space-y-3">
                        {{-- Dependência / Unidade --}}
                        @if($upload->dependency || $upload->unit)
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-900 truncate">{{ $upload->dependency }}</div>
                                    <div class="text-xs text-gray-500 truncate">{{ $upload->unit }} {{ $upload->unit_code ? '- ' . $upload->unit_code : '' }}</div>
                                </div>
                            </div>
                        @endif

                        {{-- Estatísticas --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-blue-50 rounded-lg px-3 py-2 border border-blue-100">
                                <div class="text-xs text-blue-600 font-medium mb-0.5">Itens</div>
                                <div class="text-lg font-bold text-blue-900">{{ $upload->total_items }}</div>
                            </div>
                            <div class="bg-emerald-50 rounded-lg px-3 py-2 border border-emerald-100">
                                <div class="text-xs text-emerald-600 font-medium mb-0.5">Valor Total</div>
                                <div class="text-sm font-bold text-emerald-900">R$ {{ number_format($upload->total_value, 2, ',', '.') }}</div>
                            </div>
                        </div>

                        {{-- Footer com Metadata --}}
                        <div class="flex items-center justify-between text-xs text-gray-500 pt-2 border-t border-gray-100">
                            <div class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>{{ $upload->uploader->war_name ?? '—' }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ $upload->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Actions Overlay (visível no hover) --}}
                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">
                        <a href="{{ route('inventory.download', $upload) }}"
                           onclick="event.stopPropagation();"
                           class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg shadow-lg transition-colors"
                           title="Baixar PDF">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </a>
                        @if(auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('inventory.destroy', $upload) }}"
                              onsubmit="event.stopPropagation(); return confirm('Excluir inventário {{ $upload->filename }}?');"
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg shadow-lg transition-colors"
                                    title="Excluir">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                        </form>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endif

@endsection
