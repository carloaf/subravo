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
        <x-card>
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Arquivo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Dependência / Unidade</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Itens</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Valor Total</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Uploader</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Data</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-32">Ações</th>
                    </tr>
                </x-slot:header>

                @foreach($uploads as $upload)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('inventory.show', $upload) }}" class="flex items-center gap-2 text-sm font-medium text-gray-900 hover:text-emerald-600">
                                <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span class="truncate max-w-xs" title="{{ $upload->filename }}">{{ $upload->filename }}</span>
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-gray-900">{{ $upload->dependency ?? '—' }}</div>
                            @if($upload->unit)
                                <div class="text-xs text-gray-500">{{ $upload->unit }} {{ $upload->unit_code ? '- ' . $upload->unit_code : '' }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusColor = match($upload->status) {
                                    'completed'  => 'green',
                                    'processing' => 'blue',
                                    'pending'    => 'yellow',
                                    'error'      => 'red',
                                    default      => 'gray',
                                };
                            @endphp
                            <x-badge :color="$statusColor">{{ $upload->status_label }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-semibold text-gray-900">{{ $upload->total_items }}</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">R$ {{ number_format($upload->total_value, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $upload->uploader->war_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $upload->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('inventory.download', $upload) }}"
                                   class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-1.5 rounded transition-colors"
                                   title="Baixar PDF">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </a>
                                @if(auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('inventory.destroy', $upload) }}"
                                      onsubmit="return confirm('⚠️ ATENÇÃO: Excluir inventário?\n\n📄 Arquivo: {{ $upload->filename }}\n🗃️ Itens: {{ $upload->total_items }}\n\n❌ Esta ação é IRREVERSÍVEL:\n   • Arquivo PDF será deletado\n   • Todos os registros serão removidos do banco\n\nConfirmar exclusão?');"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-600 hover:text-red-800 hover:bg-red-50 p-1.5 rounded transition-colors"
                                            title="Excluir inventário">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>
    @endif
@endif

@endsection
