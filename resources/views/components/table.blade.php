{{--
    Container de tabela genérico (wrapper responsivo + estilo).

    Slots:
    - $header — conteúdo do <thead>
    - default — conteúdo do <tbody>

    Uso:
    <x-table>
        <x-slot:header>
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nome</th>
            </tr>
        </x-slot:header>
        <tr><td class="px-4 py-3">Produto A</td></tr>
    </x-table>
--}}

@props([])

<div class="overflow-x-auto rounded-lg border border-gray-200">
    <table class="min-w-full divide-y divide-gray-200">
        @if(isset($header))
            <thead class="bg-gray-50">
                {{ $header }}
            </thead>
        @endif
        <tbody class="bg-white divide-y divide-gray-100">
            {{ $slot }}
        </tbody>
    </table>
</div>
