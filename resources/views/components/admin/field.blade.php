{{-- Label + any control + validation message. Errors use amber, never red. --}}
@props(['label' => null, 'for' => null, 'name' => null, 'hint' => null, 'required' => false])

@php $errorKey = $name ? str_replace(['[', ']'], ['.', ''], $name) : null; @endphp

<div {{ $attributes }}>
    @if ($label)
        <label for="{{ $for ?? $name }}" class="admin-label">
            {{ $label }}@if ($required)<span class="text-primary"> *</span>@endif
        </label>
    @endif
    {{ $slot }}
    @if ($errorKey && $errors->has($errorKey))
        <p class="mt-1.5 flex items-center gap-1 text-xs text-amber-700"><i class="ti ti-alert-circle"></i>{{ $errors->first($errorKey) }}</p>
    @elseif ($hint)
        <p class="mt-1.5 text-xs text-muted">{{ $hint }}</p>
    @endif
</div>
