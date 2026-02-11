{{--
    Botão reutilizável.

    Props:
    - $variant  (string) — 'primary', 'secondary', 'danger', 'success', 'outline' (default: 'primary')
    - $size     (string) — 'sm', 'md', 'lg' (default: 'md')
    - $href     (string|null) — se informado, renderiza <a> em vez de <button>
    - $type     (string) — tipo do botão (default: 'button')
    - $icon     (string|null) — SVG path para ícone à esquerda

    Uso:
    <x-btn variant="primary" icon="M12 4v16m8-8H4">Novo Produto</x-btn>
    <x-btn variant="outline" href="/products" size="sm">Ver Todos</x-btn>
--}}

@props([
    'variant' => 'primary',
    'size'    => 'md',
    'href'    => null,
    'type'    => 'button',
    'icon'    => null,
])

@php
    $base = 'inline-flex items-center justify-center font-medium rounded-lg transition focus:outline-none focus:ring-2 focus:ring-offset-2';

    $variants = [
        'primary'   => 'bg-emerald-700 text-white hover:bg-emerald-800 focus:ring-emerald-500',
        'secondary' => 'bg-gray-100 text-gray-700 hover:bg-gray-200 focus:ring-gray-400',
        'danger'    => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'success'   => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
        'outline'   => 'border border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-emerald-500',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-2.5 text-base',
    ];

    $variantClass = $variants[$variant] ?? $variants['primary'];
    $sizeClass    = $sizes[$size] ?? $sizes['md'];
    $classes      = "{$base} {$variantClass} {$sizeClass}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <svg class="w-4 h-4 mr-1.5 -ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
            </svg>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <svg class="w-4 h-4 mr-1.5 -ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
            </svg>
        @endif
        {{ $slot }}
    </button>
@endif
