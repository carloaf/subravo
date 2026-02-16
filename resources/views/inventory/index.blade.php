@extends('layouts.app')

@section('title', 'Inventário — SUBRAVO')
@section('page-title', 'Inventário SISCOFIS')

@section('header-actions')
    <div class="flex gap-2">
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

{{-- Filtros --}}
<x-card class="mb-6">
    <form method="GET" action="{{ route('inventory.index') }}" class="flex flex-col sm:flex-row gap-3">
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
            <x-btn variant="secondary" href="{{ route('inventory.index') }}" size="md">Limpar</x-btn>
        @endif
    </form>
</x-card>

{{-- Tabela de Uploads --}}
@if($uploads->isEmpty())
    <x-empty-state
        title="Nenhum inventário carregado"
        message="Carregue um PDF de Relação de Material Carga do SISCOFIS para começar."
        action="{{ route('inventory.create') }}"
        actionLabel="Carregar PDF"
    />
@else
    <x-card>
        <x-table>
            <x-slot:header>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Arquivo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Dependência / Unidade</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Itens</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Valor Total</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Carregado por</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Data</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Ações</th>
                </tr>
            </x-slot:header>

            @foreach($uploads as $upload)
                @php
                    $statusColor = match($upload->status) {
                        'completed'  => 'green',
                        'processing' => 'blue',
                        'pending'    => 'yellow',
                        'error'      => 'red',
                        default      => 'gray',
                    };
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm font-medium text-gray-900 truncate max-w-[200px]" title="{{ $upload->filename }}">
                                {{ $upload->filename }}
                            </span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        @if($upload->dependency || $upload->unit)
                            <div class="font-medium">{{ $upload->dependency }}</div>
                            <div class="text-xs text-gray-500">{{ $upload->unit }} {{ $upload->unit_code ? '- ' . $upload->unit_code : '' }}</div>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-sm font-semibold text-gray-900">
                        {{ $upload->total_items }}
                    </td>
                    <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">
                        R$ {{ number_format($upload->total_value, 2, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <x-badge :color="$statusColor">{{ $upload->status_label }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ $upload->uploader->war_name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">
                        {{ $upload->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('inventory.show', $upload) }}"
                               class="text-emerald-600 hover:text-emerald-800 text-sm font-medium transition-colors"
                               title="Ver detalhes">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                            <a href="{{ route('inventory.download', $upload) }}"
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors"
                               title="Baixar PDF">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </a>
                            @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('inventory.destroy', $upload) }}"
                                  onsubmit="return confirm('Excluir inventário {{ $upload->filename }}?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 transition-colors" title="Excluir">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

        <div class="px-4 py-3 border-t border-gray-100">
            {{ $uploads->links() }}
        </div>
    </x-card>
@endif

@endsection
