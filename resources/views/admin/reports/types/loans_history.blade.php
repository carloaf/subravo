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
        <span>
            @if($dateFrom && $dateTo)
                Período: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
            @else
                Período: Todos
            @endif
            &mdash; {{ $loans->count() }} {{ Str::plural('cautela', $loans->count()) }}
        </span>
    </div>

    <x-card>
        @if($loans->isEmpty())
            <x-empty-state title="Nenhuma cautela encontrada" message="Não há cautelas para o período selecionado." />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nº Cautela</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Mutuário</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Emissão</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Devolução</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                    </tr>
                </x-slot:header>

                @foreach($loans as $loan)
                    <tr class="border-t border-gray-100 hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-mono font-medium">
                            <a href="{{ route('loans.show', $loan) }}" class="text-emerald-600 hover:underline">{{ $loan->loan_number }}</a>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            @if($loan->borrower_type === 'individual')
                                {{ $loan->borrower?->rank?->abbreviation }} {{ $loan->borrower?->war_name ?? '—' }}
                            @else
                                {{ $loan->borrower_section }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-center text-gray-600">{{ $loan->loan_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm text-center text-gray-600">{{ $loan->actual_return_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $colors = ['active' => 'amber', 'returned' => 'green', 'partial' => 'blue', 'overdue' => 'red'];
                                $labels = ['active' => 'Ativa', 'returned' => 'Devolvida', 'partial' => 'Parcial', 'overdue' => 'Atrasada'];
                            @endphp
                            <x-badge :color="$colors[$loan->status] ?? 'gray'">
                                {{ $labels[$loan->status] ?? ucfirst($loan->status) }}
                            </x-badge>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    </x-card>
</div>

@endsection
