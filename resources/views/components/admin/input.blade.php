@props(['name', 'label' => null, 'value' => null, 'type' => 'text', 'hint' => null, 'required' => false])

@php $errorKey = str_replace(['[', ']'], ['.', ''], $name); @endphp

<x-admin.field :label="$label" :name="$name" :hint="$hint" :required="$required">
    <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}" value="{{ old($errorKey, $value) }}"
           @if ($required) required @endif
           {{ $attributes->class(['admin-input', 'border-amber-500' => $errors->has($errorKey)]) }}>
</x-admin.field>
