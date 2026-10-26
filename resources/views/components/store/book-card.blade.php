@props(['book', 'size' => 'md'])

@php
    $cheapest = $book->formats->where('is_active', true)->sortBy('price')->first();
    $discount = $cheapest?->discountPercent() ?? 0;
@endphp

<a href="{{ route('books.show', $book) }}" {{ $attributes->class('group block') }}>
    <div class="relative mb-3 aspect-[3/4] overflow-hidden rounded-md bg-page shadow-book transition duration-300 group-hover:-translate-y-1.5">
        @if ($book->coverUrl())
            <img src="{{ $book->coverUrl() }}" alt="{{ $book->title }} cover" loading="lazy" class="size-full object-cover">
        @else
            <div class="flex size-full items-center justify-center text-primary"><i class="ti ti-book text-5xl"></i></div>
        @endif
        @if ($discount)
            <span class="absolute top-2 left-2 rounded bg-amber-500 px-2 py-0.5 text-xs font-bold text-white">-{{ $discount }}%</span>
        @endif
        @if ($book->formats->contains(fn ($f) => $f->format->isDigital() && $f->is_active))
            <span class="absolute top-2 right-2 rounded bg-white/90 px-1.5 py-0.5 text-[11px] font-semibold text-primary"><i class="ti ti-device-tablet"></i> eBook</span>
        @endif
    </div>
    <h3 @class(['line-clamp-2 font-semibold text-navy group-hover:text-primary', 'text-[15px]' => $size === 'md', 'text-sm' => $size === 'sm'])>{{ $book->title }}</h3>
    <p class="mt-0.5 line-clamp-1 text-sm text-muted">{{ $book->authors->pluck('name')->join(', ') }}</p>
    @if ($cheapest)
        <p class="mt-1.5 flex items-baseline gap-2">
            <span class="font-bold text-primary">{{ money($cheapest->price) }}</span>
            @if ($discount)<span class="text-xs text-muted line-through">{{ money($cheapest->mrp) }}</span>@endif
        </p>
    @endif
</a>
