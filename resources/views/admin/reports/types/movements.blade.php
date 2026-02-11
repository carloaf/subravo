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
            &mdash; {{ $movements->count() }} {{ Str::plural('movimentação', $movements->count()) }}
        </span>
    </div>

    <x-card>
        @if($movements->isEmpty())
            <x-empty-state title="Nenhuma movimentação" message="Não há movimentações para o período selecionado." />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Data</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Produto</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Tipo</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Qtd</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Responsável</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Motivo</th>
                    </tr>
                </x-slot:header>

                @foreach($movements as $mov)
                    @php
                        $typeColors = [
                            'entry' => 'green', 'exit' => 'red', 'loan' => 'amber',
                            'return' => 'blue', 'adjustment' => 'purple', 'transfer' => 'gray',
                        ];
                        $typeLabels = [
                            'entry' => 'Entrada', 'exit' => 'Saída', 'loan' => 'Empréstimo',
                            'return' => 'Devolução', 'adjustment' => 'Ajuste', 'transfer' => 'Transferência',
                        ];
                        $isPositive = in_array($mov->type, ['entry', 'return']);
                    @endphp
                    <tr class="border-t border-gray-100 hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $mov->stockItem?->product?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <x-badge :color="$typeColors[$mov->type] ?? 'gray'">
                                {{ $typeLabels[$mov->type] ?? $mov->type }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-sm text-center font-semibold {{ $isPositive ? 'text-green-700' : 'text-red-700' }}">
                            {{ $isPositive ? '+' : '-' }}{{ $mov->quantity }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $mov->performedBy?->war_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 max-w-xs truncate">{{ $mov->reason ?? '—' }}</td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    </x-card>
</div>

@endsection
