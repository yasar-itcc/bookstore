{{-- On/off switch that always submits 0 or 1. --}}
@props(['name', 'label', 'checked' => false, 'hint' => null])

@php $errorKey = str_replace(['[', ']'], ['.', ''], $name); @endphp

<label {{ $attributes->class('flex cursor-pointer items-start gap-3') }}>
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="1" class="peer sr-only" @checked(old($errorKey, $checked))>
    <span class="relative mt-0.5 h-5 w-9 shrink-0 rounded-full bg-slate-300 transition peer-checked:bg-primary peer-focus-visible:ring-3 peer-focus-visible:ring-primary/25 after:absolute after:top-0.5 after:left-0.5 after:size-4 after:rounded-full after:bg-white after:shadow after:transition peer-checked:after:translate-x-4"></span>
    <span>
        <span class="block text-sm font-medium text-ink">{{ $label }}</span>
        @if ($hint)
            <span class="block text-xs text-muted">{{ $hint }}</span>
        @endif
    </span>
</label>
