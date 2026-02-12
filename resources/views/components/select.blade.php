{{--
    Select de formulário com label.

    Props:
    - $name     (string)
    - $label    (string)
    - $options  (array) — ['value' => 'Label', ...]
    - $selected (string|null)
    - $required (bool)
    - $disabled (bool)
    - $placeholder (string|null) — texto da opção vazia

    Uso:
    <x-select name="category_id" label="Categoria" :options="$categories" required />
--}}

@props([
    'name'        => '',
    'label'       => '',
    'options'     => [],
    'selected'    => null,
    'required'    => false,
    'disabled'    => false,
    'placeholder' => '— Selecione —',
])

<div>
    <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700 mb-2">
        {{ $label }}
        @if($required) <span class="text-red-500 ml-1">*</span> @endif
    </label>
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        style="transition: all 0.3s ease; background: {{ $disabled ? 'rgba(243, 244, 246, 0.9)' : 'rgba(255, 255, 255, 0.9)' }};"
        {{ $attributes->merge(['class' => 'w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900' . ($disabled ? ' cursor-not-allowed text-gray-500' : '')]) }}
    >
        @if($placeholder)
            <option value="" class="text-gray-400">{{ $placeholder }}</option>
        @endif
        @foreach($options as $value => $label)
            <option value="{{ $value }}" @selected(old($name, $selected) == $value)>{{ $label }}</option>
        @endforeach
    </select>
    @error($name)
        <p class="mt-1.5 text-xs text-red-600 flex items-center font-medium">
            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>
