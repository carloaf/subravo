{{--
    Empty state — exibido quando uma tabela/lista está vazia.

    Props:
    - $icon    (string|null)  — SVG path
    - $title   (string)       — título
    - $message (string|null)  — mensagem
    - $action  (string|null)  — href para botão de ação
    - $actionLabel (string|null)

    Uso:
    <x-empty-state title="Nenhum produto" message="Cadastre o primeiro produto." action="/products/create" actionLabel="Novo Produto" />
--}}

@props([
    'icon'        => 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4',
    'title'       => 'Nenhum item encontrado',
    'message'     => null,
    'action'      => null,
    'actionLabel' => 'Criar',
])

<div class="flex flex-col items-center justify-center py-12 text-center">
    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $icon }}"/>
    </svg>
    <h3 class="text-sm font-semibold text-gray-700">{{ $title }}</h3>
    @if($message)
        <p class="text-sm text-gray-400 mt-1 max-w-sm">{{ $message }}</p>
    @endif
    @if($action)
        <div class="mt-4">
            <x-btn variant="primary" href="{{ $action }}" size="sm"
                   icon="M12 4v16m8-8H4">{{ $actionLabel }}</x-btn>
        </div>
    @endif
</div>
