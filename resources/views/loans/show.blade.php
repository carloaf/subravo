@extends('layouts.app')

@section('title', 'Cautela #' . $loan->loan_number . ' — SMARTSUB')
@section('page-title', 'Cautela #' . $loan->loan_number)

@section('header-actions')
    <div class="flex items-center space-x-2">
        <a href="{{ route('loans.pdf', $loan) }}" target="_blank">
            <x-btn variant="outline" icon="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" size="sm">
                PDF Cautela
            </x-btn>
        </a>

        @if($loan->status === 'active')
            <x-btn variant="primary" href="{{ route('loans.return', $loan) }}" icon="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" size="sm">
                Registrar Devolução
            </x-btn>
        @endif
    </div>
@endsection

@section('content')

<div class="space-y-6">

    {{-- Status Badge --}}
    <div class="flex items-center space-x-3">
        @php
            $statusColors = [
                'active' => 'amber',
                'returned' => 'green',
                'partial' => 'blue',
                'overdue' => 'red',
            ];
            $statusLabels = [
                'active' => 'Ativa',
                'returned' => 'Devolvida',
                'partial' => 'Parcialmente Devolvida',
                'overdue' => 'Em Atraso',
            ];
        @endphp
        <x-badge :color="$statusColors[$loan->status] ?? 'gray'">
            {{ $statusLabels[$loan->status] ?? ucfirst($loan->status) }}
        </x-badge>
        @if($loan->status === 'overdue' && $loan->expected_return_date)
            <span class="text-xs text-red-600">
                Previsão era {{ $loan->expected_return_date->format('d/m/Y') }} — {{ $loan->expected_return_date->diffForHumans() }}
            </span>
        @endif
    </div>

    {{-- Info Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card title="Dados da Cautela">
            <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                <div>
                    <dt class="text-gray-500">Número</dt>
                    <dd class="font-semibold text-gray-900">{{ $loan->loan_number }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Data Emissão</dt>
                    <dd class="text-gray-900">{{ $loan->loan_date->format('d/m/Y') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Previsão Devol.</dt>
                    <dd class="text-gray-900">{{ $loan->expected_return_date?->format('d/m/Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Data Devolução</dt>
                    <dd class="text-gray-900">{{ $loan->actual_return_date?->format('d/m/Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Expedido por</dt>
                    <dd class="text-gray-900">{{ $loan->issuedBy?->getDisplayName() ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Devolvido a</dt>
                    <dd class="text-gray-900">{{ $loan->returnedTo?->getDisplayName() ?? '—' }}</dd>
                </div>
            </dl>
        </x-card>

        <x-card title="Mutuário">
            <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                <div>
                    <dt class="text-gray-500">Tipo</dt>
                    <dd class="text-gray-900">{{ $loan->borrower_type === 'individual' ? 'Individual' : 'Seção' }}</dd>
                </div>
                @if($loan->borrower_type === 'individual' && $loan->borrower)
                    <div>
                        <dt class="text-gray-500">Nome</dt>
                        <dd class="font-semibold text-gray-900">{{ $loan->borrower->getDisplayName() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Identidade</dt>
                        <dd class="text-gray-900">{{ $loan->borrower->identity_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Posto/Grad.</dt>
                        <dd class="text-gray-900">{{ $loan->borrower->rank?->abbreviation ?? '—' }}</dd>
                    </div>
                @else
                    <div>
                        <dt class="text-gray-500">Seção</dt>
                        <dd class="font-semibold text-gray-900">{{ $loan->borrower_section }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-gray-500">OM</dt>
                    <dd class="text-gray-900">{{ $loan->borrowerOrganization?->abbreviation ?? '—' }}</dd>
                </div>
            </dl>
        </x-card>
    </div>

    {{-- Itens --}}
    <x-card title="Itens Cautelados" subtitle="{{ $loan->items->count() }} {{ Str::plural('item', $loan->items->count()) }}">
        <x-table>
            <x-slot:header>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Produto</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Lote / Série</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Qtd</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Devolvido</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Cond. Saída</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Cond. Retorno</th>
                </tr>
            </x-slot:header>

            @foreach($loan->items as $item)
                <tr class="border-t border-gray-100 hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                        {{ $item->stockItem?->product?->name ?? 'Item removido' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">
                        @if($item->stockItem?->batch) Lote: {{ $item->stockItem->batch }} @endif
                        @if($item->stockItem?->serial_number) Nº {{ $item->stockItem->serial_number }} @endif
                        @if(!$item->stockItem?->batch && !$item->stockItem?->serial_number) — @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-center text-gray-900">{{ $item->quantity }}</td>
                    <td class="px-4 py-3 text-sm text-center">
                        <span class="{{ $item->returned_quantity >= $item->quantity ? 'text-green-600 font-semibold' : 'text-gray-600' }}">
                            {{ $item->returned_quantity }} / {{ $item->quantity }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <x-badge :color="$item->condition_out === 'bom' ? 'green' : ($item->condition_out === 'regular' ? 'amber' : 'red')">
                            {{ ucfirst($item->condition_out) }}
                        </x-badge>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($item->condition_in)
                            <x-badge :color="$item->condition_in === 'bom' ? 'green' : ($item->condition_in === 'regular' ? 'amber' : 'red')">
                                {{ ucfirst($item->condition_in) }}
                            </x-badge>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-table>
    </x-card>

    {{-- Observações --}}
    @if($loan->notes || $loan->return_notes)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @if($loan->notes)
                <x-card title="Observações da Cautela">
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $loan->notes }}</p>
                </x-card>
            @endif
            @if($loan->return_notes)
                <x-card title="Observações da Devolução">
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $loan->return_notes }}</p>
                </x-card>
            @endif
        </div>
    @endif

    {{-- Back --}}
    <div>
        <x-btn variant="secondary" href="{{ route('loans.index') }}" icon="M10 19l-7-7m0 0l7-7m-7 7h18">
            Voltar à Lista
        </x-btn>
    </div>
</div>

@endsection
