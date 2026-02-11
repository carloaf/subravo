{{--
    Input de formulário com label.

    Props:
    - $name       (string) — name/id do campo
    - $label      (string) — texto do label
    - $type       (string) — tipo do input (default: 'text')
    - $value      (string|null) — valor
    - $placeholder (string|null)
    - $required   (bool) — se é obrigatório (default: false)
    - $disabled   (bool)
    - $hint       (string|null) — texto de ajuda

    Uso:
    <x-input name="name" label="Nome do Produto" required />
    <x-input name="batch" label="Lote" hint="Opcional" />
--}}

@props([
    'name'        => '',
    'label'       => '',
    'type'        => 'text',
    'value'       => null,
    'placeholder' => null,
    'required'    => false,
    'disabled'    => false,
    'hint'        => null,
])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
        {{ $label }}
        @if($required) <span class="text-red-500">*</span> @endif
    </label>
    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes->merge(['class' => 'w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm' . ($disabled ? ' bg-gray-50 text-gray-500' : '')]) }}
    >
    @if($hint)
        <p class="mt-1 text-xs text-gray-400">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
