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
    <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700 mb-2">
        {{ $label }}
        @if($required) <span class="text-red-500 ml-1">*</span> @endif
    </label>
    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($required) required @endif
        @if($disabled) disabled @endif
        style="transition: all 0.3s ease; background: {{ $disabled ? 'rgba(243, 244, 246, 0.9)' : 'rgba(255, 255, 255, 0.9)' }};"
        {{ $attributes->merge(['class' => 'w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 placeholder-gray-400' . ($disabled ? ' cursor-not-allowed text-gray-500' : '')]) }}
    >
    @if($hint)
        <p class="mt-1.5 text-xs text-gray-500 flex items-center">
            <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            {{ $hint }}
        </p>
    @endif
    @error($name)
        <p class="mt-1.5 text-xs text-red-600 flex items-center font-medium">
            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>
