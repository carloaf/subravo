@extends('layouts.app')

@section('title', 'Cautelas — SUBRAVO')
@section('page-title', 'Cautelas (Empréstimos)')

@section('header-actions')
    <x-btn variant="primary" href="{{ route('loans.create') }}" size="sm"
           icon="M12 4v16m8-8H4">Nova Cautela</x-btn>
@endsection

@section('content')

{{-- Filtros --}}
<x-card class="mb-6">
    <form method="GET" action="{{ route('loans.index') }}" class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Buscar por nº cautela, nome ou identidade..."
                   style="transition: all 0.3s ease; background: rgba(255, 255, 255, 0.9);"
                   class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 placeholder-gray-400">
        </div>
        <div class="w-full sm:w-44">
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
            <x-btn variant="secondary" href="{{ route('loans.index') }}" size="md">Limpar</x-btn>
        @endif
    </form>
</x-card>

{{-- Tabela --}}
@if($loans->isEmpty())
    <x-empty-state
        title="Nenhuma cautela encontrada"
        message="Registre o primeiro empréstimo de material."
        action="{{ route('loans.create') }}"
        actionLabel="Nova Cautela"
    />
@else
    <x-card>
        <x-table>
            <x-slot:header>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nº Cautela</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Mutuário</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Data</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Devolução Prevista</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Responsável</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Ações</th>
                </tr>
            </x-slot:header>

            @foreach($loans as $loan)
                @php
                    $statusColor = match($loan->status) {
                        'active'   => 'blue',
                        'returned' => 'green',
                        'partial'  => 'amber',
                        'overdue'  => 'red',
                        default    => 'gray',
                    };
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <a href="{{ route('loans.show', $loan) }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-800">
                            {{ $loan->loan_number }}
                        </a>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-sm font-medium text-gray-900">{{ $loan->getBorrowerDisplayName() }}</p>
                        <p class="text-xs text-gray-400">{{ $loan->borrower_type === 'individual' ? 'Individual' : 'Seção' }}</p>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $loan->loan_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ $loan->expected_return_date?->format('d/m/Y') ?? '—' }}
                        @if($loan->isOverdue())
                            <span class="text-xs text-red-600 font-medium">(vencido)</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <x-badge :color="$statusColor">{{ $loan->getStatusLabel() }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $loan->loanedBy?->getDisplayName() ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end space-x-1">
                            <a href="{{ route('loans.show', $loan) }}" class="p-1.5 text-gray-400 hover:text-emerald-600 rounded" title="Detalhes">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('loans.pdf', $loan) }}" target="_blank" class="p-1.5 text-gray-400 hover:text-red-600 rounded" title="PDF Cautela">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </a>
                            @if($loan->status === 'active' || $loan->status === 'partial')
                                <a href="{{ route('loans.return', $loan) }}" class="p-1.5 text-gray-400 hover:text-green-600 rounded" title="Devolução">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>

        @if($loans->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $loans->links() }}
            </div>
        @endif
    </x-card>
@endif

@endsection
