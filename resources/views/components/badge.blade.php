{{--
    Badge/etiqueta de status.

    Props:
    - $color (string) — 'green', 'red', 'amber', 'blue', 'gray', 'purple' (default: 'gray')

    Uso:
    <x-badge color="green">Disponível</x-badge>
    <x-badge color="red">Vencido</x-badge>
--}}

@props([
    'color' => 'gray',
])

@php
    $colors = [
        'green'  => 'bg-green-100 text-green-800',
        'red'    => 'bg-red-100 text-red-800',
        'amber'  => 'bg-amber-100 text-amber-800',
        'blue'   => 'bg-blue-100 text-blue-800',
        'gray'   => 'bg-gray-100 text-gray-800',
        'purple' => 'bg-purple-100 text-purple-800',
    ];
    $cls = $colors[$color] ?? $colors['gray'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$cls}"]) }}>
    {{ $slot }}
</span>
