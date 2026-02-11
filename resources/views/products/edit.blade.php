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

            {{-- Ações --}}
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <form method="POST" action="{{ route('products.destroy', $product) }}"
                      onsubmit="return confirm('Tem certeza que deseja excluir este produto?')">
                    @csrf
                    @method('DELETE')
                    <x-btn variant="danger" type="submit" size="sm"
                           icon="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">Excluir</x-btn>
                </form>
                <div class="flex items-center space-x-3">
                    <x-btn variant="secondary" href="{{ route('products.show', $product) }}">Cancelar</x-btn>
                    <x-btn variant="primary" type="submit" icon="M5 13l4 4L19 7">Salvar</x-btn>
                </div>
            </div>
        </form>
    </x-card>
</div>

@endsection
