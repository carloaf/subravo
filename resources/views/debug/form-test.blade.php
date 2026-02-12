<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Formulários - SUBRAVO</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto space-y-8">
        <h1 class="text-3xl font-bold text-gray-900">🔍 Diagnóstico de Formulários</h1>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                ❌ {{ session('error') }}
            </div>
        @endif

        {{-- Teste 1: Form de Update separado --}}
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">Teste 1: Atualizar Produto (PUT)</h2>
            <form method="POST" action="{{ route('products.update', 1) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                    <input type="text" name="name" value="Produto Teste" required 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Categoria ID</label>
                    <input type="number" name="category_id" value="1" required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Unidade</label>
                    <input type="text" name="unit" value="un" required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Estoque Mínimo</label>
                    <input type="number" name="minimum_stock" value="10" required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_durable" value="1" class="mr-2">
                        <span>Uso Duradouro</span>
                    </label>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    ✅ Atualizar (deve funcionar)
                </button>
                <p class="text-sm text-gray-500">Action: {{ route('products.update', 1) }}</p>
                <p class="text-sm text-gray-500">Method: POST + _method=PUT</p>
            </form>
        </div>

        {{-- Teste 2: Form de Delete separado --}}
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">Teste 2: Excluir Produto (DELETE)</h2>
            <form method="POST" action="{{ route('products.destroy', 1) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700"
                        onclick="return confirm('Confirma exclusão?')">
                    ❌ Excluir (deve dar erro se houver estoque)
                </button>
                <p class="text-sm text-gray-500 mt-2">Action: {{ route('products.destroy', 1) }}</p>
                <p class="text-sm text-gray-500">Method: POST + _method=DELETE</p>
            </form>
        </div>

        {{-- Informações do Sistema --}}
        <div class="bg-gray-800 text-white p-6 rounded-lg">
            <h2 class="text-xl font-bold mb-4">Informações do Sistema</h2>
            <pre class="text-xs overflow-auto">Request Method: {{ request()->method() }}
Current Route: {{ request()->route()->getName() ?? 'N/A' }}
Laravel Version: {{ app()->version() }}
PHP Version: {{ PHP_VERSION }}

Produto ID 1:
@php
    $prod = \App\Models\Product::find(1);
@endphp
@if($prod)
Nome: {{ $prod->name }}
Tem itens de estoque: {{ $prod->stockItems()->count() }} itens
@else
Produto não encontrado
@endif
</pre>
        </div>

        <div class="text-center">
            <a href="{{ route('products.index') }}" class="text-blue-600 hover:underline">← Voltar para Produtos</a>
        </div>
    </div>
</body>
</html>
