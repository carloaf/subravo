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
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
        {{ $label }}
        @if($required) <span class="text-red-500">*</span> @endif
    </label>
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes->merge(['class' => 'w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm']) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $value => $label)
            <option value="{{ $value }}" @selected(old($name, $selected) == $value)>{{ $label }}</option>
        @endforeach
    </select>
    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
