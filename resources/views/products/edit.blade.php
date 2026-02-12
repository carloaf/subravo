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
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input name="siscofis_code" label="Código SISCOFIS" placeholder="Ex: 123456-7"
                             :value="$product->siscofis_code"
                             hint="Código/ficha do SISCOFIS (opcional)" />

                    <x-input name="shelf_life_months" label="Validade (meses)" type="number" placeholder="Ex: 60"
                             :value="$product->shelf_life_months"
                             hint="Prazo de validade padrão em meses" />
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

    {{-- Itens de Estoque do Produto --}}
    @if($product->stockItems->isNotEmpty())
        <x-card title="Itens de Estoque / Entradas" subtitle="Lotes e entradas individuais deste produto" class="mt-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Lote/Série</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Data Entrada SISCOFIS</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Validade</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Localização</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Quantidade</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($product->stockItems as $item)
                            <tr x-data="{ editing: false }" class="hover:bg-gray-50">
                                {{-- Modo Visualização --}}
                                <template x-if="!editing">
                                    <td colspan="7" class="px-4 py-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-6 text-sm">
                                                <div>
                                                    <span class="font-medium text-gray-900">{{ $item->serial_number ?? $item->batch ?? '—' }}</span>
                                                    @if($item->serial_number && $item->batch)
                                                        <span class="text-gray-400 text-xs ml-1">({{ $item->batch }})</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <span class="text-gray-500">Entrada:</span>
                                                    <span class="font-medium text-gray-900">{{ $item->siscofis_entry_date ? $item->siscofis_entry_date->format('d/m/Y') : '—' }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500">Validade:</span>
                                                    <span class="font-medium {{ $item->isExpired() ? 'text-red-600' : 'text-gray-900' }}">
                                                        {{ $item->expiration_date ? $item->expiration_date->format('d/m/Y') : '—' }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500">Local:</span>
                                                    <span class="text-gray-900">{{ $item->location ?? '—' }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500">Qtd:</span>
                                                    <span class="font-semibold text-gray-900">{{ $item->quantity }}</span>
                                                </div>
                                                <div>
                                                    @php
                                                        $statusColors = [
                                                            'available' => 'green',
                                                            'loaned' => 'blue',
                                                            'damaged' => 'red',
                                                            'decommissioned' => 'gray',
                                                        ];
                                                        $statusLabels = [
                                                            'available' => 'Disponível',
                                                            'loaned' => 'Emprestado',
                                                            'damaged' => 'Danificado',
                                                            'decommissioned' => 'Baixado',
                                                        ];
                                                    @endphp
                                                    <x-badge :color="$statusColors[$item->status] ?? 'gray'">
                                                        {{ $statusLabels[$item->status] ?? $item->status }}
                                                    </x-badge>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <button @click="editing = true" 
                                                        class="p-1.5 text-gray-400 hover:text-emerald-600 rounded transition" 
                                                        title="Editar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                                <a href="{{ route('stock.show', $item) }}" 
                                                   class="p-1.5 text-gray-400 hover:text-blue-600 rounded transition"
                                                   title="Ver detalhes">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </template>

                                {{-- Modo Edição --}}
                                <template x-if="editing">
                                    <td colspan="7" class="px-4 py-4 bg-emerald-50">
                                        <form method="POST" action="{{ route('stock.updateItem', $item) }}" class="space-y-3">
                                            @csrf
                                            @method('PUT')
                                            
                                            <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Lote</label>
                                                    <input type="text" name="batch" value="{{ $item->batch }}"
                                                           class="w-full px-3 py-1.5 text-sm rounded border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Nº Série</label>
                                                    <input type="text" name="serial_number" value="{{ $item->serial_number }}"
                                                           class="w-full px-3 py-1.5 text-sm rounded border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Data Entrada SISCOFIS</label>
                                                    <input type="date" name="siscofis_entry_date" 
                                                           value="{{ $item->siscofis_entry_date?->format('Y-m-d') }}"
                                                           class="w-full px-3 py-1.5 text-sm rounded border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Localização</label>
                                                    <input type="text" name="location" value="{{ $item->location }}"
                                                           class="w-full px-3 py-1.5 text-sm rounded border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Observações</label>
                                                    <input type="text" name="notes" value="{{ $item->notes }}"
                                                           placeholder="Observações..."
                                                           class="w-full px-3 py-1.5 text-sm rounded border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-center justify-end space-x-2 pt-2 border-t border-emerald-200">
                                                <button type="button" @click="editing = false"
                                                        class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">
                                                    Cancelar
                                                </button>
                                                <button type="submit"
                                                        class="px-3 py-1.5 text-sm font-medium text-white bg-emerald-600 border border-transparent rounded-md hover:bg-emerald-700 transition">
                                                    Salvar Alterações
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </template>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 px-4 py-3 bg-gray-50 border-t border-gray-200 text-sm text-gray-600">
                <strong>Total:</strong> {{ $product->stockItems->count() }} 
                {{ Str::plural('entrada', $product->stockItems->count()) }} registrada(s) para este produto.
            </div>
        </x-card>
    @endif

    {{-- Form de exclusão separado (submetido via JavaScript) --}}
    <form id="delete-form" method="POST" action="{{ route('products.destroy', $product) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>

@endsection
