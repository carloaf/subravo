@extends('layouts.app')

@section('title', $reportTitle . ' - HelpSub')
@section('page-title', $reportTitle)

@section('header-actions')
    <x-btn variant="secondary" href="{{ route('admin.reports.index') }}" icon="M10 19l-7-7m0 0l7-7m-7 7h18" size="sm">
        Voltar
    </x-btn>
@endsection

@section('content')

<div class="space-y-4">
    {{-- Meta --}}
    <div class="flex items-center justify-between text-xs text-gray-500">
        <span>Gerado em {{ $generatedAt }} por {{ $generatedBy }}</span>
        <span>Total: {{ $products->count() }} {{ Str::plural('produto', $products->count()) }}</span>
    </div>

    {{-- Totais --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <x-stat-card title="Total Disponível" :value="$totalAvailable" color="green" icon="M5 13l4 4L19 7" />
        <x-stat-card title="Total Emprestado" :value="$totalLoaned" color="amber" icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-stat-card title="Abaixo do Mínimo" :value="$products->where('below_minimum', true)->count()" color="red" icon="M12 9v2m0 4h.01" />
    </div>

    {{-- Tabela --}}
    <x-card>
        <x-table>
            <x-slot:header>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Produto</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Categoria</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Unid.</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Disponível</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Emprestado</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Mínimo</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                </tr>
            </x-slot:header>

            @foreach($products as $prod)
                <tr class="border-t border-gray-100 hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $prod['name'] }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $prod['category'] }}</td>
                    <td class="px-4 py-3 text-sm text-center text-gray-600">{{ $prod['unit'] }}</td>
                    <td class="px-4 py-3 text-sm text-center font-semibold text-green-700">{{ $prod['available'] }}</td>
                    <td class="px-4 py-3 text-sm text-center text-amber-700">{{ $prod['loaned'] }}</td>
                    <td class="px-4 py-3 text-sm text-center text-gray-600">{{ $prod['minimum'] }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($prod['below_minimum'])
                            <x-badge color="red">Abaixo</x-badge>
                        @else
                            <x-badge color="green">OK</x-badge>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-table>
    </x-card>
</div>

@endsection
