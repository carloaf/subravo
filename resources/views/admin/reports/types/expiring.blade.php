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
        <span class="text-amber-600 font-semibold">{{ $items->count() }} {{ Str::plural('item', $items->count()) }} próximos da validade (60 dias)</span>
    </div>

    <x-card>
        @if($items->isEmpty())
            <x-empty-state title="Nenhum item vencendo" message="Não há itens com validade nos próximos 60 dias." />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Produto</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Categoria</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Lote</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Qtd</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Validade</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Dias Rest.</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Localização</th>
                    </tr>
                </x-slot:header>

                @foreach($items as $item)
                    @php
                        $daysLeft = (int) now()->diffInDays($item->expiration_date, false);
                        $isExpired = $daysLeft < 0;
                    @endphp
                    <tr class="border-t border-gray-100 {{ $isExpired ? 'bg-red-50' : ($daysLeft <= 15 ? 'bg-amber-50' : 'hover:bg-gray-50') }}">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            <a href="{{ route('stock.show', $item) }}" class="text-emerald-600 hover:underline">
                                {{ $item->product?->name ?? '—' }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $item->product?->category?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $item->batch ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-center text-gray-700">{{ $item->quantity }}</td>
                        <td class="px-4 py-3 text-sm text-center font-semibold {{ $isExpired ? 'text-red-700' : 'text-amber-700' }}">
                            {{ $item->expiration_date->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($isExpired)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                    VENCIDO
                                </span>
                            @elseif($daysLeft <= 15)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                    {{ $daysLeft }}d
                                </span>
                            @else
                                <span class="text-sm text-gray-600">{{ $daysLeft }}d</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $item->location ?? '—' }}</td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    </x-card>
</div>

@endsection
