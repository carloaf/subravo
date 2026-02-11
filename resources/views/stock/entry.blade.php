@extends('layouts.app')

@section('title', 'Entrada de Material — SUBRAVO')
@section('page-title', 'Entrada de Material')

@section('content')

<div class="max-w-2xl mx-auto">
    <x-card title="Registrar Entrada" subtitle="Entrada de novo material no estoque">
        <form method="POST" action="{{ route('stock.storeEntry') }}" class="space-y-5">
            @csrf

            <x-select name="product_id" label="Produto"
                      :options="$products->pluck('name', 'id')->toArray()" required />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input name="quantity" label="Quantidade" type="number" value="1" required />
                <x-input name="batch" label="Lote" placeholder="Ex: LOTE-2026-001" hint="Opcional" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input name="serial_number" label="Número de Série" placeholder="Se aplicável" hint="Para itens serializados" />
                <x-input name="expiration_date" label="Data de Validade" type="date" hint="Se perecível" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input name="siscofis_entry_date" label="Data Entrada SISCOFIS" type="date" hint="Data do registro no SISCOFIS" />
                <x-input name="location" label="Localização" placeholder="Ex: Prateleira A3, Armário 02" />
            </div>

            <x-input name="subunit" label="Subunidade" placeholder="Ex: 1ª Cia, SApInt" hint="Opcional" />

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                <textarea id="notes" name="notes" rows="3" placeholder="Observações sobre a entrada..."
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">{{ old('notes') }}</textarea>
                @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Ações --}}
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                <x-btn variant="secondary" href="{{ route('stock.index') }}">Cancelar</x-btn>
                <x-btn variant="primary" type="submit" icon="M5 13l4 4L19 7">Registrar Entrada</x-btn>
            </div>
        </form>
    </x-card>
</div>

@endsection
