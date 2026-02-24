@extends('layouts.app')

@section('title', $reportTitle . ' — SMARTSUB')
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
        <span class="text-red-600 font-semibold">{{ $products->count() }} {{ Str::plural('produto', $products->count()) }} abaixo do mínimo</span>
    </div>

    <x-card>
        @if($products->isEmpty())
            <x-empty-state title="Estoque adequado!" message="Nenhum produto está abaixo do estoque mínimo. Parabéns!"
                           icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Produto</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Categoria</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Disponível</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Mínimo</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Déficit</th>
                    </tr>
                </x-slot:header>

                @foreach($products as $product)
                    @php
                        $available = $product->getAvailableStock();
                        $deficit = $product->minimum_stock - $available;
                    @endphp
                    <tr class="border-t border-gray-100 hover:bg-red-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            <a href="{{ route('products.show', $product) }}" class="text-emerald-600 hover:underline">{{ $product->name }}</a>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $product->category?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-center font-semibold text-red-700">{{ $available }}</td>
                        <td class="px-4 py-3 text-sm text-center text-gray-600">{{ $product->minimum_stock }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                -{{ $deficit }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    </x-card>
</div>

@endsection
