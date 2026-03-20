@extends('layouts.app')

@section('title', 'Catalogo de Itens - HelpSub')
@section('page-title', 'Catálogo de Itens')

@section('header-actions')
    <x-btn variant="primary" href="{{ route('products.create') }}" size="sm"
           icon="M12 4v16m8-8H4">Novo Item</x-btn>
@endsection

@section('content')

{{-- Filtros --}}
<x-card class="mb-6">
    <form method="GET" action="{{ route('products.index') }}" class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Buscar por nome, descrição ou código SISCOFIS..."
                   class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 placeholder-gray-400">
        </div>
        <div class="w-full sm:w-48">
            <select name="category_id"
                    class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900">
                <option value="">Todas as categorias</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-full sm:w-40">
            <select name="type"
                    class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900">
                <option value="">Todos os tipos</option>
                <option value="durable"    @selected(request('type') === 'durable')>Uso Duradouro</option>
                <option value="consumable" @selected(request('type') === 'consumable')>Consumível</option>
            </select>
        </div>
        <x-btn type="submit" variant="outline" size="md" icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z">Buscar</x-btn>
        @if(request()->hasAny(['search', 'category_id', 'type']))
            <x-btn variant="secondary" href="{{ route('products.index') }}" size="md">Limpar</x-btn>
        @endif
    </form>
</x-card>

{{-- Tabela --}}
@if($products->isEmpty())
    <x-empty-state
        title="Nenhum item encontrado"
        message="Cadastre o primeiro item do catálogo para começar."
        action="{{ route('products.create') }}"
        actionLabel="Novo Item"
    />
@else
    <x-card>
        <x-table>
            <x-slot:header>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nome</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Categoria</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Unidade</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">SISCOFIS</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Tipo</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Ações</th>
                </tr>
            </x-slot:header>

            @foreach($products as $product)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <a href="{{ route('products.show', $product) }}" class="text-sm font-medium text-gray-900 hover:text-emerald-600">
                            {{ $product->name }}
                        </a>
                        @if($product->description)
                            <p class="text-xs text-gray-400 mt-0.5 truncate max-w-xs">{{ $product->description }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $product->category->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600 text-center">{{ $product->unit }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500 font-mono">{{ $product->siscofis_code ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($product->is_durable)
                            <x-badge color="emerald">Duradouro</x-badge>
                        @else
                            <x-badge color="gray">Consumível</x-badge>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end space-x-1">
                            <a href="{{ route('products.show', $product) }}" class="p-1.5 text-gray-400 hover:text-emerald-600 rounded" title="Detalhes">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('products.edit', $product) }}" class="p-1.5 text-gray-400 hover:text-amber-600 rounded" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>

        @if($products->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $products->links() }}
            </div>
        @endif
    </x-card>

    <p class="mt-3 text-xs text-gray-400 text-right">{{ $products->total() }} iten(s) no catálogo</p>
@endif

@endsection
