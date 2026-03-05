@extends('layouts.app')

@section('title', 'Nova Cautela — SMARTSUB')
@section('page-title', 'Nova Cautela')

@section('content')

@php
    function shortProductName(string $name): string {
        if (!str_contains($name, '/') && !preg_match('/Cor:|Tipo:|Tecido:|Tamanho:/i', $name)) {
            return $name;
        }
        $parts   = explode('/', $name, 2);
        $base    = trim($parts[0]);
        $rest    = $parts[1] ?? $name;
        $cor = $tamanho = '';
        if (preg_match('/Cor:\s*([^;]+)/i', $rest, $cm))     $cor     = trim($cm[1]);
        if (preg_match('/Tamanho:\s*([^;]+)/i', $rest, $tm)) $tamanho = trim($tm[1]);
        return trim($base . ($cor ? ' ' . $cor : '') . ($tamanho ? ' ' . $tamanho : ''));
    }
@endphp

<form method="POST" action="{{ route('loans.store') }}" class="space-y-6" x-data="loanForm()">
    @csrf

    {{-- ── Dados do Mutuário ── --}}
    <x-card title="Dados do Mutuário" subtitle="Quem receberá o material emprestado">
        <div class="space-y-5">

            {{-- Tipo --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tipo de Empréstimo <span class="text-red-500">*</span>
                </label>
                <div class="flex space-x-6">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="borrower_type" value="individual" x-model="borrowerType"
                               class="text-emerald-600 focus:ring-emerald-500"
                               @checked(old('borrower_type', 'individual') === 'individual')>
                        <span class="text-sm text-gray-700">Individual (pessoa)</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="borrower_type" value="section" x-model="borrowerType"
                               class="text-emerald-600 focus:ring-emerald-500"
                               @checked(old('borrower_type') === 'section')>
                        <span class="text-sm text-gray-700">Seção / Subunidade</span>
                    </label>
                </div>
                @error('borrower_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- ── Individual ── --}}
            <div x-show="borrowerType === 'individual'" x-transition x-data="borrowerSearch()" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Busca por identidade militar --}}
                    <div class="relative">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Mutuário <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="search"
                               @input.debounce.300ms="fetchResults()"
                               @focus="open = results.length > 0"
                               @click.away="open = false"
                               placeholder="Digite a identidade ou nome de guerra..."
                               style="transition: all 0.3s ease; background: rgba(255,255,255,0.9);"
                               class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 placeholder-gray-400"
                               autocomplete="off">
                        <input type="hidden" name="borrower_user_id" :value="selectedId">

                        <div x-show="open && results.length > 0" x-transition
                             class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-auto">
                            <template x-for="user in results" :key="user.id">
                                <button type="button" @click="selectUser(user)"
                                        class="w-full text-left px-3 py-2 text-sm hover:bg-emerald-50"
                                        x-text="user.label"></button>
                            </template>
                        </div>

                        <div x-show="selectedId" class="mt-1.5 flex items-center text-xs text-emerald-700 bg-emerald-50 px-2.5 py-1.5 rounded-lg border border-emerald-200">
                            <svg class="w-3.5 h-3.5 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                            </svg>
                            <span x-text="selectedLabel" class="flex-1"></span>
                            <button type="button" @click="clearSelection()" class="ml-2 text-gray-400 hover:text-red-500">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div x-show="loading" class="mt-1 text-xs text-gray-400">Buscando...</div>
                        @error('borrower_user_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <x-select name="borrower_organization_id" label="OM do Mutuário"
                              :options="$organizations->pluck('abbreviation', 'id')->toArray()"
                              :selected="old('borrower_organization_id')" />
                </div>

                {{-- CPF · Idt Militar · Telefone --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">CPF</label>
                        <input type="text" name="borrower_cpf" value="{{ old('borrower_cpf') }}"
                               placeholder="000.000.000-00"
                               style="transition: all 0.3s ease; background: rgba(255,255,255,0.9);"
                               class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 placeholder-gray-400"
                               maxlength="14">
                        @error('borrower_cpf') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Identidade Militar</label>
                        <input type="text" name="borrower_identity_display"
                               x-model="selectedIdentity"
                               placeholder="Preenchido ao selecionar ou informe manualmente"
                               style="transition: all 0.3s ease; background: rgba(255,255,255,0.9);"
                               class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 placeholder-gray-400"
                               maxlength="20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Telefone</label>
                        <input type="text" name="borrower_phone" value="{{ old('borrower_phone') }}"
                               placeholder="(61) 99999-9999"
                               style="transition: all 0.3s ease; background: rgba(255,255,255,0.9);"
                               class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 placeholder-gray-400"
                               maxlength="20">
                        @error('borrower_phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ── Seção ── --}}
            <div x-show="borrowerType === 'section'" x-cloak x-transition class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input name="borrower_section" label="Seção / Subunidade"
                              placeholder="Ex: 1ª Cia, SApInt, Pel Cmdo" />
                    <x-select name="borrower_organization_id" label="OM"
                              :options="$organizations->pluck('abbreviation', 'id')->toArray()"
                              :selected="old('borrower_organization_id')" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">CPF do Responsável</label>
                        <input type="text" name="borrower_cpf" value="{{ old('borrower_cpf') }}"
                               placeholder="000.000.000-00"
                               style="transition: all 0.3s ease; background: rgba(255,255,255,0.9);"
                               class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 placeholder-gray-400"
                               maxlength="14">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Identidade Militar</label>
                        <input type="text" placeholder="Nº da identidade"
                               style="transition: all 0.3s ease; background: rgba(255,255,255,0.9);"
                               class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900"
                               maxlength="30">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Telefone</label>
                        <input type="text" name="borrower_phone" value="{{ old('borrower_phone') }}"
                               placeholder="(61) 99999-9999"
                               style="transition: all 0.3s ease; background: rgba(255,255,255,0.9);"
                               class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900"
                               maxlength="20">
                    </div>
                </div>
            </div>

            {{-- Data de Devolução --}}
            <div class="pt-3 border-t border-gray-100">
                <x-input name="expected_return_date" label="Data Prevista de Devolução" type="date"
                         hint="Opcional — se preenchido, o sistema alertará sobre atrasos" />
            </div>

        </div>
    </x-card>

    {{-- ── Itens da Cautela ── --}}
    <x-card title="Itens do Empréstimo" subtitle="Selecione os itens e quantidades a emprestar">

        <template x-for="(item, index) in items" :key="index">
            <div class="mb-4 p-4 bg-gray-50 rounded-xl border border-gray-200">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">

                    {{-- Item de Estoque --}}
                    <div class="sm:col-span-7">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                            Item de Estoque <span class="text-red-500">*</span>
                        </label>
                        <select :name="`items[${index}][stock_item_id]`" x-model="item.stock_item_id" required
                                style="transition: all 0.3s ease; background: rgba(255,255,255,0.9);"
                                class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900">
                            <option value="">— Selecione o material —</option>
                            @foreach($products as $product)
                                @if($product->stockItems->isNotEmpty())
                                    @php
                                        $shortName = shortProductName($product->name);
                                    @endphp
                                    @foreach($product->stockItems as $si)
                                        @php
                                            // Mostrar lote apenas se não for gerado pelo SISCOFIS (FICHA-xxx)
                                            $showBatch = $si->batch && !str_starts_with($si->batch, 'FICHA-') && !str_starts_with($si->batch, 'INV-');
                                            $lbl  = $showBatch             ? '  [Lote: ' . $si->batch . ']' : '';
                                            $lbl .= $si->serial_number     ? '  Nº ' . $si->serial_number  : '';
                                            $lbl .= '  ' . $si->quantity . ' un';
                                            $lbl .= $si->unit_cost         ? '  R$ ' . number_format($si->unit_cost, 2, ',', '.') : '';
                                        @endphp
                                        <option value="{{ $si->id }}">{{ $shortName }}{{ $lbl }}</option>
                                    @endforeach
                                @endif
                            @endforeach
                        </select>
                    </div>

                    {{-- Quantidade --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                            Qtd <span class="text-red-500">*</span>
                        </label>
                        <input type="number" :name="`items[${index}][quantity]`"
                               x-model="item.quantity" min="1" required
                               style="transition: all 0.3s ease; background: rgba(255,255,255,0.9);"
                               class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-center font-semibold text-gray-900">
                    </div>

                    {{-- Condição Saída --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                            Condição <span class="text-red-500">*</span>
                        </label>
                        <select :name="`items[${index}][condition_out]`" x-model="item.condition_out" required
                                style="transition: all 0.3s ease; background: rgba(255,255,255,0.9);"
                                class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900">
                            @foreach(\App\Models\LoanItem::CONDITIONS as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Remover --}}
                    <div class="sm:col-span-1 flex items-end justify-center">
                        <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                title="Remover item">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>

                </div>
            </div>
        </template>

        <button type="button" @click="addItem()"
                class="w-full py-3 border-2 border-dashed border-gray-300 rounded-xl text-sm font-medium text-gray-500 hover:border-emerald-400 hover:text-emerald-600 hover:bg-emerald-50/40 transition-all flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Adicionar Item
        </button>

        @error('items') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
    </x-card>

    {{-- ── Observações ── --}}
    <x-card title="Observações">
        <textarea name="notes" rows="3"
                  placeholder="Observações sobre o empréstimo (opcional)"
                  style="transition: all 0.3s ease; background: rgba(255,255,255,0.9);"
                  class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 placeholder-gray-400 resize-none">{{ old('notes') }}</textarea>
    </x-card>

    {{-- ── Ações ── --}}
    <div class="flex items-center justify-end space-x-3">
        <x-btn variant="secondary" href="{{ route('loans.index') }}">Cancelar</x-btn>
        <x-btn variant="primary" type="submit"
               icon="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
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
        selectedIdentity: '',

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
            this.selectedId       = user.id;
            this.selectedLabel    = user.label;
            this.selectedIdentity = user.identity_number ?? '';
            this.search           = '';
            this.results          = [];
            this.open             = false;
        },

        clearSelection() {
            this.selectedId       = '';
            this.selectedLabel    = '';
            this.selectedIdentity = '';
            this.search           = '';
        }
    };
}
</script>
@endpush
