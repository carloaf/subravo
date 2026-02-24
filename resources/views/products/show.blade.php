@extends('layouts.app')

@section('title', $product->name . ' — SMARTSUB')
@section('page-title', $product->name)

@section('header-actions')
    <div class="flex items-center gap-2">
        <x-btn variant="outline" href="{{ route('products.edit', $product) }}" size="sm"
               icon="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">Editar</x-btn>
        <x-btn variant="secondary" href="{{ route('products.index') }}" size="sm"
               icon="M10 19l-7-7m0 0l7-7m-7 7h18">Voltar</x-btn>
    </div>
@endsection

@section('content')

<div class="max-w-2xl mx-auto">
    <x-card title="Informações do Item">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

            <div class="sm:col-span-2">
                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Nome</p>
                <p class="text-base font-semibold text-gray-900 mt-0.5">{{ $product->name }}</p>
            </div>

            @if($product->description)
                <div class="sm:col-span-2">
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Descrição</p>
                    <p class="text-sm text-gray-700 mt-0.5">{{ $product->description }}</p>
                </div>
            @endif

            <div>
                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Categoria</p>
                <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $product->category->name ?? '—' }}</p>
            </div>

            <div>
                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Unidade de Medida</p>
                <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $product->unit }}</p>
            </div>

            <div>
                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Código SISCOFIS</p>
                <p class="text-sm font-mono text-gray-900 mt-0.5">{{ $product->siscofis_code ?? '—' }}</p>
            </div>

            <div>
                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Validade Padrão</p>
                <p class="text-sm text-gray-900 mt-0.5">
                    {{ $product->shelf_life_months ? $product->shelf_life_months . ' meses' : '—' }}
                </p>
            </div>

            <div>
                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Tipo de Material</p>
                <div class="mt-1">
                    @if($product->is_durable)
                        <x-badge color="emerald">Uso Duradouro</x-badge>
                    @else
                        <x-badge color="gray">Consumível</x-badge>
                    @endif
                </div>
            </div>

            <div>
                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Serializado</p>
                <p class="text-sm text-gray-900 mt-0.5">{{ $product->is_serialized ? 'Sim' : 'Não' }}</p>
            </div>

            <div>
                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Cadastrado em</p>
                <p class="text-sm text-gray-900 mt-0.5">{{ $product->created_at->format('d/m/Y') }}</p>
            </div>

        </div>
    </x-card>
</div>

@endsection
