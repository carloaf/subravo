@extends('layouts.app')

@section('title', 'Ajuste de Estoque - HelpSub')
@section('page-title', 'Ajuste de Estoque')

@section('content')

<div class="max-w-2xl mx-auto">
    <x-card title="Ajustar Item" subtitle="{{ $stockItem->product->name }}">

        {{-- Info atual --}}
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                <div>
                    <p class="text-xs text-gray-400 uppercase">Quantidade Atual</p>
                    <p class="text-lg font-bold text-gray-900">{{ $stockItem->quantity }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase">Status Atual</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $stockItem->getStatusLabel() }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase">Lote</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $stockItem->batch ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase">Localização</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $stockItem->location ?? '—' }}</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('stock.storeAdjust', $stockItem) }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input name="new_quantity" label="Nova Quantidade" type="number"
                         :value="$stockItem->quantity" required />

                @php
                    $statusOptions = \App\Models\StockItem::STATUSES;
                @endphp
                <x-select name="new_status" label="Novo Status"
                          :options="$statusOptions" :selected="$stockItem->status" required />
            </div>

            <div>
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">
                    Motivo do Ajuste <span class="text-red-500">*</span>
                </label>
                <textarea id="reason" name="reason" rows="3" required
                          placeholder="Descreva o motivo do ajuste (inventário, correção, perda, etc.)"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">{{ old('reason') }}</textarea>
                @error('reason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Ações --}}
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                <x-btn variant="secondary" href="{{ route('stock.show', $stockItem) }}">Cancelar</x-btn>
                <x-btn variant="primary" type="submit" icon="M5 13l4 4L19 7">Confirmar Ajuste</x-btn>
            </div>
        </form>
    </x-card>
</div>

@endsection
