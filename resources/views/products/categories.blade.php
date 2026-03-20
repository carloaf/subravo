@extends('layouts.app')

@section('title', 'Categorias - HelpSub')
@section('page-title', 'Categorias')

@section('content')

<div class="max-w-3xl mx-auto">

    {{-- Formulário de nova categoria --}}
    <x-card title="Nova Categoria" class="mb-6">
        <form method="POST" action="{{ route('categories.store') }}" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <div class="flex-1">
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Nome da categoria"
                       required
                       style="transition: all 0.3s ease; background: rgba(255, 255, 255, 0.9);"
                       class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 placeholder-gray-400">
                @error('name') <p class="mt-1 text-xs text-red-600 flex items-center font-medium">
                    <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </p> @enderror
            </div>
            <div class="flex-1">
                <input type="text" name="description" value="{{ old('description') }}" placeholder="Descrição (opcional)"
                       style="transition: all 0.3s ease; background: rgba(255, 255, 255, 0.9);"
                       class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 placeholder-gray-400">
            </div>
            <x-btn variant="primary" type="submit" icon="M12 4v16m8-8H4">Criar</x-btn>
        </form>
    </x-card>

    {{-- Lista de categorias --}}
    @if($categories->isEmpty())
        <x-empty-state
            title="Nenhuma categoria cadastrada"
            message="Crie a primeira categoria usando o formulário acima."
        />
    @else
        <x-card title="Categorias Cadastradas" subtitle="{{ $categories->count() }} categoria(s)">
            <div class="divide-y divide-gray-100 -mx-6 -mb-6">
                @foreach($categories as $category)
                    <div class="px-6 py-4" x-data="{ editing: false }">
                        {{-- Modo visualização --}}
                        <div x-show="!editing" class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $category->name }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $category->description ?? 'Sem descrição' }}
                                    &middot; {{ $category->products_count }} produto(s)
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button @click="editing = true" class="p-1.5 text-gray-400 hover:text-amber-600 rounded" title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                @if($category->products_count === 0)
                                    <form method="POST" action="{{ route('categories.destroy', $category) }}"
                                          onsubmit="return confirm('Excluir categoria {{ $category->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded" title="Excluir">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        {{-- Modo edição --}}
                        <div x-show="editing" x-cloak>
                            <form method="POST" action="{{ route('categories.update', $category) }}" class="flex flex-col sm:flex-row gap-3">
                                @csrf
                                @method('PUT')
                                <div class="flex-1">
                                    <input type="text" name="name" value="{{ $category->name }}" required
                                           style="transition: all 0.3s ease; background: rgba(255, 255, 255, 0.9);"
                                           class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900">
                                </div>
                                <div class="flex-1">
                                    <input type="text" name="description" value="{{ $category->description }}"
                                           placeholder="Descrição"
                                           style="transition: all 0.3s ease; background: rgba(255, 255, 255, 0.9);"
                                           class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 placeholder-gray-400">
                                </div>
                                <div class="flex items-center space-x-2">
                                    <x-btn variant="primary" type="submit" size="sm">Salvar</x-btn>
                                    <x-btn variant="secondary" @click="editing = false" size="sm">Cancelar</x-btn>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    @endif
</div>

@endsection
