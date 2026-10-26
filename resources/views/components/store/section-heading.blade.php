@props(['title', 'link' => null, 'linkText' => 'View all'])

<div {{ $attributes->class('mb-8 flex flex-wrap items-end justify-between gap-4') }}>
    <h2 class="section-title">{{ $title }}</h2>
    <div class="flex items-center gap-3">
        {{ $slot }}
        @if ($link)
            <a href="{{ $link }}" class="btn-store px-5 py-2.5 text-xs">{{ $linkText }}</a>
        @endif
    </div>
</div>
