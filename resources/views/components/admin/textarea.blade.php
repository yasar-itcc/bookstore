@props(['name', 'label' => null, 'value' => null, 'rows' => 4, 'hint' => null, 'required' => false])

<x-admin.field :label="$label" :name="$name" :hint="$hint" :required="$required">
    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" @if ($required) required @endif
              {{ $attributes->class(['admin-input', 'border-amber-500' => $errors->has($name)]) }}>{{ old($name, $value) }}</textarea>
</x-admin.field>
