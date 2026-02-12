@extends('layouts.app')

@section('title', 'Editar Produto — SUBRAVO')
@section('page-title', 'Editar Produto')

@section('content')

<div class="max-w-2xl mx-auto">
    <x-card title="Editar Produto" subtitle="{{ $product->name }}">
        <form method="POST" action="{{ route('products.update', $product) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <x-input name="name" label="Nome do Produto" :value="$product->name" required />

            <x-input name="description" label="Descrição" :value="$product->description" />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-select name="category_id" label="Categoria"
                          :options="$categories->pluck('name', 'id')->toArray()"
                          :selected="$product->category_id" required />

                <x-select name="unit" label="Unidade de Medida"
                          :options="array_combine($units, $units)"
                          :selected="$product->unit" required />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input name="minimum_stock" label="Estoque Mínimo" type="number"
                         :value="$product->minimum_stock" required />

                <div class="flex items-end pb-1">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="hidden" name="is_serialized" value="0">
                        <input type="checkbox" name="is_serialized" value="1"
                               @checked(old('is_serialized', $product->is_serialized))
                               class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                        <span class="text-sm text-gray-700">Produto serializado</span>
                    </label>
                </div>
            </div>

            {{-- Campos SISCOFIS --}}
            <div class="border-t border-gray-200 pt-5 mt-2">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Dados SISCOFIS e Validade</h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <x-input name="siscofis_code" label="Código SISCOFIS" placeholder="Ex: 123456-7"
                             :value="$product->siscofis_code"
                             hint="Código/ficha do SISCOFIS (opcional)" />

                    <x-input name="shelf_life_months" label="Validade (meses)" type="number" placeholder="Ex: 12"
                             :value="$product->shelf_life_months"
                             hint="Prazo de validade em meses (opcional)" />

                    <x-input name="reference_entry_date" label="Data Entrada SISCOFIS" type="date"
                             :value="$product->reference_entry_date?->format('Y-m-d')"
                             hint="Data de entrada de referência (opcional)" />
                </div>

                <div class="mt-4">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="hidden" name="is_durable" value="0">
                        <input type="checkbox" name="is_durable" value="1"
                               @checked(old('is_durable', $product->is_durable))
                               class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                        <span class="text-sm font-medium text-gray-700">Uso Duradouro</span>
                        <span class="text-xs text-gray-500">(Material metálico, ferramentas, equipamentos permanentes)</span>
                    </label>
                </div>
            </div>

            {{-- Ações --}}
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <button type="button"
                        onclick="if(confirm('Tem certeza que deseja excluir este produto?')) { document.getElementById('delete-form').submit(); }"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Excluir
                </button>
                <div class="flex items-center space-x-3">
                    <x-btn variant="secondary" href="{{ route('products.show', $product) }}">Cancelar</x-btn>
                    <x-btn variant="primary" type="submit" icon="M5 13l4 4L19 7">Salvar</x-btn>
                </div>
            </div>
        </form>
    </x-card>

    {{-- Form de exclusão separado (submetido via JavaScript) --}}
    <form id="delete-form" method="POST" action="{{ route('products.destroy', $product) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>

@endsection
