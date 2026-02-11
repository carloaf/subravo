{{--
    Card genérico — container branco com título e slot de conteúdo.

    Props:
    - $title    (string|null)  — título do card
    - $subtitle (string|null)  — subtítulo
    - $padding  (string)       — padding class (default: 'p-6')
    - $class    (string)       — classes adicionais no container

    Slots:
    - $actions — botões/links no canto superior direito
    - default  — conteúdo do card

    Uso:
    <x-card title="Meu Card">
        <x-slot:actions><a href="#">Ação</a></x-slot:actions>
        Conteúdo aqui...
    </x-card>
--}}

@props([
    'title'    => null,
    'subtitle' => null,
    'padding'  => 'p-6',
    'class'    => '',
])

<div {{ $attributes->merge(['class' => "bg-white rounded-xl shadow-sm border border-gray-200 {$class}"]) }}>
    @if($title)
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div>
                <h3 class="text-base font-semibold text-gray-900">{{ $title }}</h3>
                @if($subtitle)
                    <p class="text-sm text-gray-500 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @if(isset($actions))
                <div class="flex items-center space-x-2">{{ $actions }}</div>
            @endif
        </div>
    @endif
    <div class="{{ $padding }}">
        {{ $slot }}
    </div>
</div>
