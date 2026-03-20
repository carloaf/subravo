@extends('layouts.app')

@section('title', 'Editar Localizacao - HelpSub')
@section('page-title', 'Editar Localização do Inventário')

@section('header-actions')
    <x-btn variant="secondary" href="{{ route('inventory.show', $inventory) }}" size="sm"
           icon="M15 19l-7-7 7-7">
        Voltar
    </x-btn>
@endsection

@section('content')

<div class="max-w-3xl mx-auto">
    {{-- Informações do Upload --}}
    <x-card class="mb-6">
        <div class="flex items-center gap-4">
            <div class="bg-emerald-100 p-3 rounded-lg">
                <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-bold text-gray-900">{{ $inventory->filename }}</h3>
                <p class="text-sm text-gray-600">
                    Carregado em {{ $inventory->created_at->format('d/m/Y H:i') }} • 
                    {{ $inventory->total_items }} item(ns)
                </p>
            </div>
        </div>
    </x-card>

    {{-- Alerta --}}
    <div class="mb-6">
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm text-blue-800">
                    <p class="font-semibold mb-1">Por que editar a localização?</p>
                    <p>Alguns PDFs do SISCOFIS não seguem o padrão esperado, impedindo a extração automática da dependência e unidade. Preencha manualmente as informações abaixo para que os materiais possam ser corretamente identificados e filtrados.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Formulário --}}
    <x-card>
        <form method="POST" action="{{ route('inventory.update-location', $inventory) }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <div>
                <label for="dependency" class="block text-sm font-semibold text-gray-700 mb-2">
                    Dependência
                </label>
                <input type="text" 
                       id="dependency" 
                       name="dependency" 
                       value="{{ old('dependency', $inventory->dependency) }}"
                       placeholder="Ex: 11º D Sup, 1ª Cia Sup, etc."
                       class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 placeholder-gray-400"
                       autofocus>
                @error('dependency')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">
                    A dependência principal responsável pelo material (ex: 11º Depósito de Suprimento).
                </p>
            </div>

            <div>
                <label for="unit" class="block text-sm font-semibold text-gray-700 mb-2">
                    Unidade / Seção
                </label>
                <input type="text" 
                       id="unit" 
                       name="unit" 
                       value="{{ old('unit', $inventory->unit) }}"
                       placeholder="Ex: 1ª Companhia de Suprimento, Academia, Alojamento Oficiais, etc."
                       class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 placeholder-gray-400">
                @error('unit')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">
                    A unidade ou seção específica onde o material está localizado.
                </p>
            </div>

            <div>
                <label for="unit_code" class="block text-sm font-semibold text-gray-700 mb-2">
                    Código da Unidade <span class="text-gray-400 font-normal">(Opcional)</span>
                </label>
                <input type="text" 
                       id="unit_code" 
                       name="unit_code" 
                       value="{{ old('unit_code', $inventory->unit_code) }}"
                       placeholder="Ex: 37, 01, etc."
                       class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 placeholder-gray-400">
                @error('unit_code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">
                    Código numérico da unidade, se aplicável.
                </p>
            </div>

            {{-- Botões --}}
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <x-btn type="button" variant="secondary" href="{{ route('inventory.show', $inventory) }}" size="md">
                    Cancelar
                </x-btn>
                <x-btn type="submit" variant="primary" size="md" 
                       icon="M5 13l4 4L19 7">
                    Salvar Localização
                </x-btn>
            </div>
        </form>
    </x-card>
</div>

@endsection
