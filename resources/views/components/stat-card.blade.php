{{--
    Stat Card — card compacto para indicadores no dashboard.

    Props:
    - $title   (string) — título do indicador
    - $value   (string) — valor principal
    - $icon    (string) — SVG path do ícone (stroke)
    - $color   (string) — cor: 'green', 'blue', 'amber', 'red', 'purple' (default: 'green')
    - $subtitle (string|null) — texto auxiliar abaixo do valor

    Uso:
    <x-stat-card title="Produtos" value="42" icon="M20 7l-8-4-8 4..." color="green" subtitle="8 categorias" />
--}}

@props([
    'title'    => '',
    'value'    => '0',
    'icon'     => '',
    'color'    => 'green',
    'subtitle' => null,
])

@php
    $palette = match($color) {
        'blue'   => ['bg' => 'bg-blue-50',   'icon' => 'text-blue-500',   'ring' => 'border-blue-100'],
        'amber'  => ['bg' => 'bg-amber-50',  'icon' => 'text-amber-500',  'ring' => 'border-amber-100'],
        'red'    => ['bg' => 'bg-red-50',     'icon' => 'text-red-500',    'ring' => 'border-red-100'],
        'purple' => ['bg' => 'bg-purple-50',  'icon' => 'text-purple-500', 'ring' => 'border-purple-100'],
        default  => ['bg' => 'bg-emerald-50', 'icon' => 'text-emerald-600','ring' => 'border-emerald-100'],
    };
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex items-start space-x-4">
    <div class="flex-shrink-0 w-11 h-11 rounded-lg {{ $palette['bg'] }} {{ $palette['ring'] }} border flex items-center justify-center">
        <svg class="w-6 h-6 {{ $palette['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
        </svg>
    </div>
    <div class="min-w-0">
        <p class="text-sm text-gray-500 font-medium">{{ $title }}</p>
        <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ $value }}</p>
        @if($subtitle)
            <p class="text-xs text-gray-400 mt-1">{{ $subtitle }}</p>
        @endif
    </div>
</div>
