@extends('layouts.app')

@section('title', 'Nova Cautela — SUBRAVO')
@section('page-title', 'Nova Cautela')

@section('content')

<form method="POST" action="{{ route('loans.store') }}" class="space-y-6" x-data="loanForm()">
    @csrf

    {{-- Dados do Mutuário --}}
    <x-card title="Dados do Mutuário" subtitle="Quem receberá o material emprestado">
        <div class="space-y-4">
            {{-- Tipo de mutuário --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Empréstimo <span class="text-red-500">*</span></label>
                <div class="flex space-x-4">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="borrower_type" value="individual" x-model="borrowerType"
                               class="text-emerald-600 focus:ring-emerald-500" @checked(old('borrower_type', 'individual') === 'individual')>
                        <span class="text-sm text-gray-700">Individual (pessoa)</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="borrower_type" value="section" x-model="borrowerType"
                               class="text-emerald-600 focus:ring-emerald-500" @checked(old('borrower_type') === 'section')>
                        <span class="text-sm text-gray-700">Seção / Subunidade</span>
                    </label>
                </div>
                @error('borrower_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Individual --}}
            <div x-show="borrowerType === 'individual'" x-transition class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Busca por identidade militar --}}
                <div x-data="borrowerSearch()" class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Mutuário <span class="text-red-500">*</span>
                    </label>
                    <input type="text" x-model="search" @input.debounce.300ms="fetchResults()"
                           @focus="open = results.length > 0"
                           @click.away="open = false"
                           placeholder="Digite a identidade ou nome de guerra..."
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm"
                           autocomplete="off">
                    <input type="hidden" name="borrower_user_id" :value="selectedId">

                    {{-- Resultados --}}
                    <div x-show="open && results.length > 0" x-transition
                         class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-auto">
                        <template x-for="user in results" :key="user.id">
                            <button type="button" @click="selectUser(user)"
                                    class="w-full text-left px-3 py-2 text-sm hover:bg-emerald-50 focus:bg-emerald-50"
                                    x-text="user.label">
                            </button>
                        </template>
                    </div>

                    {{-- Selecionado --}}
                    <div x-show="selectedId" class="mt-1 flex items-center text-xs text-emerald-700 bg-emerald-50 px-2 py-1 rounded">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                        </svg>
                        <span x-text="selectedLabel"></span>
                        <button type="button" @click="clearSelection()" class="ml-auto text-gray-400 hover:text-red-500">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Loading --}}
                    <div x-show="loading" class="mt-1 text-xs text-gray-400">Buscando...</div>

                    @error('borrower_user_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <x-select name="borrower_organization_id" label="OM do Mutuário"
                          :options="$organizations->pluck('abbreviation', 'id')->toArray()"
                          :selected="old('borrower_organization_id')" />
            </div>

            {{-- Seção --}}
            <div x-show="borrowerType === 'section'" x-cloak x-transition class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input name="borrower_section" label="Seção / Subunidade" placeholder="Ex: 1ª Cia, SApInt, Pel Cmdo" />

                <x-select name="borrower_organization_id" label="OM"
                          :options="$organizations->pluck('abbreviation', 'id')->toArray()"
                          :selected="old('borrower_organization_id')" />
            </div>

            <x-input name="expected_return_date" label="Data Prevista de Devolução" type="date"
                     hint="Opcional — se preenchido, o sistema alertará sobre atrasos" />
        </div>
    </x-card>

    {{-- Itens da Cautela --}}
    <x-card title="Itens do Empréstimo" subtitle="Selecione os itens e quantidades a emprestar">
        <template x-for="(item, index) in items" :key="index">
            <div class="flex flex-col sm:flex-row gap-3 mb-4 p-3 bg-gray-50 rounded-lg">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Item de Estoque</label>
                    <select :name="`items[${index}][stock_item_id]`" x-model="item.stock_item_id" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                        <option value="">— Selecione —</option>
                        @foreach($products as $product)
                            @if($product->stockItems->isNotEmpty())
                                <optgroup label="{{ $product->name }}">
                                    @foreach($product->stockItems as $si)
                                        <option value="{{ $si->id }}">
                                            {{ $product->name }}
                                            @if($si->batch) — Lote: {{ $si->batch }} @endif
                                            @if($si->serial_number) — Nº {{ $si->serial_number }} @endif
                                            (disp: {{ $si->quantity }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="w-24">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Qtd</label>
                    <input type="number" :name="`items[${index}][quantity]`" x-model="item.quantity" min="1" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                </div>
                <div class="w-40">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Condição Saída</label>
                    <select :name="`items[${index}][condition_out]`" x-model="item.condition_out" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                        @foreach(\App\Models\LoanItem::CONDITIONS as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                            class="p-2 text-red-400 hover:text-red-600 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </template>

        <button type="button" @click="addItem()"
                class="w-full py-2.5 border-2 border-dashed border-gray-300 rounded-lg text-sm text-gray-500 hover:border-emerald-400 hover:text-emerald-600 transition">
            + Adicionar Item
        </button>

        @error('items') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
    </x-card>

    {{-- Observações --}}
    <x-card title="Observações">
        <textarea name="notes" rows="3" placeholder="Observações sobre o empréstimo (opcional)"
                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">{{ old('notes') }}</textarea>
    </x-card>

    {{-- Ações --}}
    <div class="flex items-center justify-end space-x-3">
        <x-btn variant="secondary" href="{{ route('loans.index') }}">Cancelar</x-btn>
        <x-btn variant="primary" type="submit" icon="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
            Registrar Cautela
        </x-btn>
    </div>
</form>

@endsection

@push('scripts')
<script>
function loanForm() {
    return {
        borrowerType: '{{ old('borrower_type', 'individual') }}',
        items: [{ stock_item_id: '', quantity: 1, condition_out: 'bom' }],
        addItem() {
            this.items.push({ stock_item_id: '', quantity: 1, condition_out: 'bom' });
        },
        removeItem(index) {
            this.items.splice(index, 1);
        }
    };
}

function borrowerSearch() {
    return {
        search: '',
        results: [],
        open: false,
        loading: false,
        selectedId: '{{ old('borrower_user_id', '') }}',
        selectedLabel: '',

        async fetchResults() {
            if (this.search.length < 2) {
                this.results = [];
                this.open = false;
                return;
            }
            this.loading = true;
            try {
                const response = await fetch(`{{ route('loans.searchBorrower') }}?q=${encodeURIComponent(this.search)}`);
                this.results = await response.json();
                this.open = this.results.length > 0;
            } catch (e) {
                this.results = [];
            }
            this.loading = false;
        },

        selectUser(user) {
            this.selectedId = user.id;
            this.selectedLabel = user.label;
            this.search = '';
            this.results = [];
            this.open = false;
        },

        clearSelection() {
            this.selectedId = '';
            this.selectedLabel = '';
            this.search = '';
        }
    };
}
</script>
@endpush
