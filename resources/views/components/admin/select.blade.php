{{-- $options: [value => label]. $placeholder adds an empty first option. --}}
@props(['name', 'label' => null, 'options' => [], 'value' => null, 'placeholder' => null, 'hint' => null, 'required' => false])

@php $selected = (string) old($name, $value); @endphp

<x-admin.field :label="$label" :name="$name" :hint="$hint" :required="$required">
    <select id="{{ $name }}" name="{{ $name }}" @if ($required) required @endif
            {{ $attributes->class(['admin-input', 'border-amber-500' => $errors->has($name)]) }}>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($selected === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
</x-admin.field>
