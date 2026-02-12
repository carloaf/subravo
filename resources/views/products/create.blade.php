@extends('layouts.app')

@section('title', 'Novo Produto — SUBRAVO')
@section('page-title', 'Novo Produto')

@section('content')

<div class="max-w-2xl mx-auto">
    <x-card title="Cadastrar Produto" subtitle="Preencha os dados do novo produto">
        <form method="POST" action="{{ route('products.store') }}" class="space-y-5">
            @csrf

            <x-input name="name" label="Nome do Produto" placeholder="Ex: Cobertura camuflada" required />

            <x-input name="description" label="Descrição" placeholder="Descrição opcional do produto" />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-select name="category_id" label="Categoria"
                          :options="$categories->pluck('name', 'id')->toArray()" required />

                <x-select name="unit" label="Unidade de Medida"
                          :options="array_combine($units, $units)" required />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input name="minimum_stock" label="Estoque Mínimo" type="number" value="0"
                         hint="Alerta quando estoque ficar abaixo desse valor" required />

                <div class="flex items-end pb-1">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="hidden" name="is_serialized" value="0">
                        <input type="checkbox" name="is_serialized" value="1"
                               @checked(old('is_serialized'))
                               class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                        <span class="text-sm text-gray-700">Produto serializado (nº série individual)</span>
                    </label>
                </div>
            </div>

            {{-- Campos SISCOFIS --}}
            <div class="border-t border-gray-200 pt-5 mt-2">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Dados SISCOFIS e Validade</h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <x-input name="siscofis_code" label="Código SISCOFIS" placeholder="Ex: 123456-7"
                             hint="Código/ficha do SISCOFIS (opcional)" />

                    <x-input name="shelf_life_months" label="Validade (meses)" type="number" placeholder="Ex: 12"
                             hint="Prazo de validade em meses (opcional)" />

                    <x-input name="reference_entry_date" label="Data Entrada SISCOFIS" type="date"
                             hint="Data de entrada de referência (opcional)" />
                </div>

                <div class="mt-4">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="hidden" name="is_durable" value="0">
                        <input type="checkbox" name="is_durable" value="1"
                               @checked(old('is_durable'))
                               class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                        <span class="text-sm font-medium text-gray-700">Uso Duradouro</span>
                        <span class="text-xs text-gray-500">(Material metálico, ferramentas, equipamentos permanentes)</span>
                    </label>
                </div>
            </div>

            {{-- Ações --}}
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                <x-btn variant="secondary" href="{{ route('products.index') }}">Cancelar</x-btn>
                <x-btn variant="primary" type="submit" icon="M5 13l4 4L19 7">Cadastrar</x-btn>
            </div>
        </form>
    </x-card>
</div>

@endsection
