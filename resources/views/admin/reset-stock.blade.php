@extends('layouts.app')

@section('title', 'Reset de Dados - HelpSub')
@section('page-title', 'Reset de Dados de Estoque')

@section('header-actions')
    <x-btn variant="secondary" href="{{ route('admin.users.index') }}" size="sm">← Voltar</x-btn>
@endsection

@section('content')

{{-- Aviso principal --}}
<div class="mb-6 bg-red-50 border-2 border-red-400 rounded-xl p-6">
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0 bg-red-100 rounded-full p-3">
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-.834-1.964-.834-2.732 0L3.082 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <div>
            <h2 class="text-xl font-bold text-red-900 mb-1">Operação Destrutiva e Irreversível</h2>
            <p class="text-red-800 text-sm">
                Apaga permanentemente os dados selecionados. Use apenas para reprocessar o inventário do zero.
                Os arquivos de inventário (PDFs e registros de upload) <strong>não serão deletados</strong>.
            </p>
        </div>
    </div>
</div>

{{-- Contadores atuais --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    <div class="bg-white rounded-xl border border-red-100 shadow-sm p-4">
        <div class="text-xs font-semibold text-red-700 uppercase mb-1">Movimentações</div>
        <div class="text-3xl font-bold text-red-600">{{ number_format($counts['movements']) }}</div>
        <div class="text-xs text-gray-400 mt-1">stock_movements</div>
    </div>
    <div class="bg-white rounded-xl border border-orange-100 shadow-sm p-4">
        <div class="text-xs font-semibold text-orange-700 uppercase mb-1">Itens de Estoque</div>
        <div class="text-3xl font-bold text-orange-600">{{ number_format($counts['stock_items']) }}</div>
        <div class="text-xs text-gray-400 mt-1">stock_items</div>
    </div>
    <div class="bg-white rounded-xl border border-purple-100 shadow-sm p-4">
        <div class="text-xs font-semibold text-purple-700 uppercase mb-1">Produtos</div>
        <div class="text-3xl font-bold text-purple-600">{{ number_format($counts['products']) }}</div>
        <div class="text-xs text-gray-400 mt-1">products</div>
    </div>
    <div class="bg-white rounded-xl border border-violet-100 shadow-sm p-4">
        <div class="text-xs font-semibold text-violet-700 uppercase mb-1">Uso Duradouro</div>
        <div class="text-3xl font-bold text-violet-600">{{ number_format($counts['durable']) }}</div>
        <div class="text-xs text-gray-400 mt-1">durable_goods_inventory</div>
    </div>
    <div class="bg-white rounded-xl border border-blue-100 shadow-sm p-4">
        <div class="text-xs font-semibold text-{{ $counts['loans'] > 0 ? 'yellow' : 'blue' }}-700 uppercase mb-1">Cautelas</div>
        <div class="text-3xl font-bold text-{{ $counts['loans'] > 0 ? 'yellow' : 'blue' }}-600">{{ number_format($counts['loans']) }}</div>
        <div class="text-xs text-gray-400 mt-1">loans</div>
    </div>
    <div class="bg-white rounded-xl border border-blue-100 shadow-sm p-4">
        <div class="text-xs font-semibold text-blue-700 uppercase mb-1">Itens Cautela</div>
        <div class="text-3xl font-bold text-blue-600">{{ number_format($counts['loan_items']) }}</div>
        <div class="text-xs text-gray-400 mt-1">loan_items</div>
    </div>
    <div class="bg-white rounded-xl border border-green-100 shadow-sm p-4">
        <div class="text-xs font-semibold text-green-700 uppercase mb-1">Inventários (mantidos)</div>
        <div class="text-3xl font-bold text-green-600">{{ number_format($counts['inv_uploads']) }}</div>
        <div class="text-xs text-gray-400 mt-1">inventory_uploads</div>
    </div>
    <div class="bg-white rounded-xl border border-green-100 shadow-sm p-4">
        <div class="text-xs font-semibold text-green-700 uppercase mb-1">Itens Inventário (mantidos)</div>
        <div class="text-3xl font-bold text-green-600">{{ number_format($counts['inv_items']) }}</div>
        <div class="text-xs text-gray-400 mt-1">inventory_items</div>
    </div>
</div>

{{-- Aviso de reset global (admin sem subunit) --}}
@if($globalReset)
<div class="mb-6 bg-orange-50 border-l-4 border-orange-500 rounded-r-lg p-4 text-sm text-orange-900">
    <strong>⚠ Modo Reset Global:</strong> Sua conta não possui subunidade configurada.
    Esta operação irá remover os dados de <strong>todas as subunidades</strong>.
</div>
@endif

{{-- Aviso de cautelas ativas --}}
@if($counts['loans'] > 0)
<div class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 rounded-r-lg p-4 text-sm text-yellow-800">
    <strong>⚠ Existe(m) {{ $counts['loans'] }} cautela(s) ativa(s).</strong>
    As cautelas e seus itens serão removidos automaticamente junto com o estoque (integridade referencial).
</div>
@endif

{{-- Resultado do último reset --}}
@if(session('reset_stats'))
@php $rs = session('reset_stats'); @endphp
<div class="mb-6 bg-green-50 border border-green-300 rounded-xl p-4">
    <h4 class="text-sm font-bold text-green-800 mb-2">✅ Reset executado com sucesso! Registros removidos:</h4>
    <div class="flex flex-wrap gap-2 text-sm">
        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full">Movimentações: <strong>{{ $rs['movements'] ?? 0 }}</strong></span>
        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full">Estoque: <strong>{{ $rs['stock_items'] ?? 0 }}</strong></span>
        @isset($rs['products'])  <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full">Produtos: <strong>{{ $rs['products'] }}</strong></span> @endisset
        @isset($rs['durable'])   <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full">Uso Duradouro: <strong>{{ $rs['durable'] }}</strong></span> @endisset
        @isset($rs['loans'])     <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full">Cautelas: <strong>{{ $rs['loans'] }}</strong></span> @endisset
    </div>
    <p class="text-xs text-green-700 mt-3">
        <strong>Próximo passo:</strong> Abra o inventário → <em>Reprocessar</em> → <em>Sync Duráveis</em>.
    </p>
</div>
@endif

{{-- Formulário --}}
<div x-data="resetForm()">
<form method="POST" action="{{ route('admin.reset-stock.execute') }}" x-ref="form">
    @csrf

    <x-card padding="">
        <div class="px-5 py-4 bg-gray-50 border-b border-gray-200 rounded-t-xl">
            <h3 class="text-sm font-bold text-gray-800 uppercase">O que será zerado</h3>
        </div>
        <div class="p-5 space-y-3">

            {{-- Estoque (obrigatório) --}}
            <div class="flex items-start gap-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <input type="checkbox" checked disabled class="mt-1 w-4 h-4 text-red-600 rounded cursor-not-allowed">
                <input type="hidden" name="reset_stock" value="1">
                <div>
                    <p class="text-sm font-bold text-red-800">Estoque <span class="font-normal text-red-600">(obrigatório)</span></p>
                    <p class="text-xs text-red-700 mt-0.5">
                        <strong>stock_items</strong> ({{ number_format($counts['stock_items']) }}) +
                        <strong>stock_movements</strong> ({{ number_format($counts['movements']) }})
                    </p>
                </div>
            </div>

            {{-- Produtos --}}
            <div class="flex items-start gap-4 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                <input id="reset_products" name="reset_products" type="checkbox" value="1" checked
                       class="mt-1 w-4 h-4 text-purple-600 rounded cursor-pointer">
                <div>
                    <label for="reset_products" class="text-sm font-bold text-purple-800 cursor-pointer">Produtos</label>
                    <p class="text-xs text-purple-700 mt-0.5">
                        <strong>products</strong> ({{ number_format($counts['products']) }}) — recriado ao "Sync Duráveis"
                    </p>
                </div>
            </div>

            {{-- Uso Duradouro --}}
            <div class="flex items-start gap-4 p-4 bg-violet-50 border border-violet-200 rounded-lg">
                <input id="reset_durable" name="reset_durable" type="checkbox" value="1" checked
                       class="mt-1 w-4 h-4 text-violet-600 rounded cursor-pointer">
                <div>
                    <label for="reset_durable" class="text-sm font-bold text-violet-800 cursor-pointer">Uso Duradouro</label>
                    <p class="text-xs text-violet-700 mt-0.5">
                        <strong>durable_goods_inventory</strong> ({{ number_format($counts['durable']) }}) — recriado ao "Reprocessar" o inventário
                    </p>
                </div>
            </div>

            {{-- Cautelas --}}
            <div class="flex items-start gap-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <input id="reset_loans" name="reset_loans" type="checkbox" value="1"
                       class="mt-1 w-4 h-4 text-yellow-600 rounded cursor-pointer">
                <div>
                    <label for="reset_loans" class="text-sm font-bold text-gray-700 cursor-pointer">
                        Cautelas
                        @if($counts['loans'] > 0)
                            <span class="ml-1 text-xs bg-yellow-200 text-yellow-900 px-2 py-0.5 rounded-full">{{ $counts['loans'] }} ativas</span>
                        @endif
                    </label>
                    <p class="text-xs text-gray-500 mt-0.5">
                        <strong>loans</strong> + <strong>loan_items</strong> ({{ number_format($counts['loan_items']) }} itens)
                        @if($counts['loans'] > 0) — serão removidas de qualquer forma pela FK @endif
                    </p>
                </div>
            </div>

        </div>
    </x-card>

    {{-- Confirmação textual --}}
    <x-card padding="" class="mt-4">
        <div class="p-5">
            <label class="block text-sm font-bold text-gray-800 mb-2">
                🔐 Para confirmar, digite exatamente:
                <code class="ml-1 bg-gray-100 px-2 py-0.5 rounded text-red-700 font-mono tracking-widest">CONFIRMAR RESET</code>
            </label>
            <input type="text" name="confirm_text"
                   x-model="confirmText"
                   placeholder="Digite aqui..."
                   :class="confirmText === 'CONFIRMAR RESET' ? 'border-green-500 bg-green-50 text-green-800' : 'border-gray-300'"
                   class="w-full max-w-sm px-4 py-2 border-2 rounded-lg focus:outline-none font-mono text-center tracking-wider transition-colors"
                   autocomplete="off">
            @error('confirm_text')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </x-card>

    <div class="mt-6 flex justify-end">
        <button type="button" @click="submitForm($refs.form)"
                :disabled="confirmText !== 'CONFIRMAR RESET'"
                :class="confirmText === 'CONFIRMAR RESET' ? 'bg-red-600 hover:bg-red-700 cursor-pointer shadow-lg' : 'bg-gray-300 cursor-not-allowed'"
                class="inline-flex items-center gap-2 px-6 py-3 text-white font-bold rounded-xl transition-all text-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Executar Reset
        </button>
    </div>
</form>
</div>

<script>
function resetForm() {
    return {
        confirmText: '',
        submitForm(form) {
            if (this.confirmText !== 'CONFIRMAR RESET') return;
            if (!confirm('\u26a0\ufe0f \u00daltima confirma\u00e7\u00e3o\n\nOs dados ser\u00e3o permanentemente deletados.\n\nDeseja prosseguir?')) return;
            form.submit();
        }
    }
}
</script>

@endsection
