@extends('layouts.app')

@section('title', $reportTitle . ' — SUBRAVO')
@section('page-title', $reportTitle)

@section('header-actions')
    <x-btn variant="secondary" href="{{ route('admin.reports.index') }}" icon="M10 19l-7-7m0 0l7-7m-7 7h18" size="sm">
        Voltar
    </x-btn>
@endsection

@section('content')

<div class="space-y-4">
    <div class="flex items-center justify-between text-xs text-gray-500">
        <span>Gerado em {{ $generatedAt }} por {{ $generatedBy }}</span>
        <span>Total: {{ $loans->count() }} {{ Str::plural('cautela', $loans->count()) }} ativas</span>
    </div>

    <x-card>
        @if($loans->isEmpty())
            <x-empty-state title="Nenhuma cautela ativa" message="Não há cautelas em aberto no momento." />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nº Cautela</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Mutuário</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">OM</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Data</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Prev. Devol.</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Itens</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                    </tr>
                </x-slot:header>

                @foreach($loans as $loan)
                    <tr class="border-t border-gray-100 hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-mono font-medium text-gray-900">
                            <a href="{{ route('loans.show', $loan) }}" class="text-emerald-600 hover:underline">{{ $loan->loan_number }}</a>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            @if($loan->borrower_type === 'individual')
                                {{ $loan->borrower?->rank?->abbreviation }} {{ $loan->borrower?->war_name ?? '—' }}
                            @else
                                {{ $loan->borrower_section }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $loan->borrowerOrganization?->abbreviation ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-center text-gray-600">{{ $loan->loan_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm text-center">
                            @if($loan->expected_return_date)
                                <span class="{{ $loan->expected_return_date->isPast() ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                    {{ $loan->expected_return_date->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-center text-gray-700">{{ $loan->items->count() }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($loan->isOverdue())
                                <x-badge color="red">Atrasada</x-badge>
                            @else
                                <x-badge color="amber">Ativa</x-badge>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    </x-card>
</div>

@endsection
