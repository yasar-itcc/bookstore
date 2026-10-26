@props(['icon' => 'ti-books', 'title', 'text' => null])

<div class="flex flex-col items-center justify-center px-6 py-14 text-center">
    <span class="mb-4 flex size-14 items-center justify-center rounded-full bg-primary-soft text-primary">
        <i class="ti {{ $icon }} text-3xl"></i>
    </span>
    <p class="text-base font-semibold text-navy">{{ $title }}</p>
    @if ($text)
        <p class="mt-1 max-w-sm text-sm text-muted">{{ $text }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
