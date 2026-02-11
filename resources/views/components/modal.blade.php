{{--
    Modal com Alpine.js.

    Props:
    - $name    (string) — identificador Alpine (x-data name)
    - $title   (string) — título do modal
    - $maxWidth (string) — 'sm', 'md', 'lg', 'xl', '2xl' (default: 'lg')

    Slots:
    - default — conteúdo do modal
    - $footer — botões de ação no rodapé

    Uso:
    <x-modal name="confirmDelete" title="Confirmar Exclusão">
        <p>Deseja realmente excluir?</p>
        <x-slot:footer>
            <x-btn variant="secondary" @click="$dispatch('close-modal')">Cancelar</x-btn>
            <x-btn variant="danger">Excluir</x-btn>
        </x-slot:footer>
    </x-modal>

    Abrir: $dispatch('open-modal', 'confirmDelete')
--}}

@props([
    'name'     => 'modal',
    'title'    => '',
    'maxWidth' => 'lg',
])

@php
    $widths = [
        'sm'  => 'max-w-sm',
        'md'  => 'max-w-md',
        'lg'  => 'max-w-lg',
        'xl'  => 'max-w-xl',
        '2xl' => 'max-w-2xl',
    ];
    $width = $widths[$maxWidth] ?? $widths['lg'];
@endphp

<div x-data="{ open: false }"
     x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
     x-on:close-modal.window="open = false"
     x-on:keydown.escape.window="open = false"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4">

    {{-- Backdrop --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false"
         class="absolute inset-0 bg-black/50"></div>

    {{-- Panel --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative w-full {{ $width }} bg-white rounded-xl shadow-xl">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
            <button @click="open = false" class="p-1 text-gray-400 hover:text-gray-600 rounded-md hover:bg-gray-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-4">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        @if(isset($footer))
            <div class="flex items-center justify-end space-x-2 px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
