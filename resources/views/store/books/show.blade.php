@php
    $formats = $book->formats->where('is_active', true)->sortBy('price')->values();
    $details = array_filter([
        'Author' => $book->authors->pluck('name')->join(', '),
        'Publisher' => $book->publisher?->name,
        'Language' => $book->language?->name,
        'Published' => $book->published_year,
        'Pages' => $book->pages,
        'ISBN' => $book->isbn,
        'Category' => $book->category?->fullName(),
    ]);
@endphp

<x-store.layout :title="$book->title" :description="Str::limit($book->description, 155)" :image="$book->coverUrl()">
    <section class="bg-page">
        <nav class="store-container flex flex-wrap items-center gap-2 py-4 text-sm text-muted" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-primary">Home</a><i class="ti ti-chevron-right text-xs"></i>
            <a href="{{ route('books.index') }}" class="hover:text-primary">Books</a><i class="ti ti-chevron-right text-xs"></i>
            @if ($book->category)<a href="{{ route('books.index', ['category' => $book->category->slug]) }}" class="hover:text-primary">{{ $book->category->name }}</a><i class="ti ti-chevron-right text-xs"></i>@endif
            <span class="text-navy">{{ Str::limit($book->title, 40) }}</span>
        </nav>
    </section>

    <section class="store-container grid gap-12 py-12 lg:grid-cols-[380px_1fr]">
        <div>
            <div class="sticky top-28 rounded-2xl bg-gradient-to-br from-primary-soft to-amber-50 p-10">
                @if ($book->coverUrl())
                    <img src="{{ $book->coverUrl() }}" alt="{{ $book->title }} cover" class="mx-auto w-full max-w-72 rounded-md shadow-book">
                @else
                    <div class="mx-auto flex aspect-[3/4] max-w-72 items-center justify-center rounded-md bg-white text-primary"><i class="ti ti-book text-7xl"></i></div>
                @endif
            </div>
        </div>

        <div x-data="{ selected: {{ $formats->first()?->id ?? 'null' }} }">
            <div class="mb-3 flex flex-wrap gap-2">
                @if ($book->is_bestseller)<span class="rounded bg-leaf px-2.5 py-0.5 text-xs font-bold tracking-wide text-white uppercase">Bestseller</span>@endif
                @if ($book->is_featured)<span class="rounded bg-amber-500 px-2.5 py-0.5 text-xs font-bold tracking-wide text-white uppercase">Featured</span>@endif
            </div>
            <h1 class="font-serif text-4xl leading-tight font-medium text-navy md:text-5xl">{{ $book->title }}</h1>
            @if ($book->subtitle)<p class="mt-2 text-xl text-muted">{{ $book->subtitle }}</p>@endif
            <p class="mt-3 text-lg">by
                @forelse ($book->authors as $author)
                    <a href="{{ route('authors.show', $author) }}" class="font-semibold text-primary hover:underline">{{ $author->name }}</a>{{ $loop->last ? '' : ',' }}
                @empty
                    <span class="font-semibold text-muted">Unknown author</span>
                @endforelse
            </p>

            @if ($formats->isNotEmpty())
                <div class="mt-8">
                    <p class="mb-3 font-semibold text-navy">Choose a format</p>
                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach ($formats as $format)
                            @php $outOfStock = ! $format->format->isDigital() && ! $format->stock; @endphp
                            <button type="button" @click="selected = {{ $format->id }}" @disabled($outOfStock)
                                    class="rounded-xl border-2 p-4 text-left transition disabled:cursor-not-allowed disabled:opacity-50"
                                    :class="selected === {{ $format->id }} ? 'border-primary bg-primary-soft/50' : 'border-line hover:border-primary/40'">
                                <span class="flex items-center gap-2 text-sm text-muted"><i class="ti {{ $format->format->icon() }} text-lg text-primary"></i>{{ $format->format->label() }}</span>
                                <span class="mt-1 block text-2xl font-bold text-navy">{{ money($format->price) }}</span>
                                @if ($format->discountPercent())
                                    <span class="text-sm"><span class="text-muted line-through">{{ money($format->mrp) }}</span> <span class="font-semibold text-amber-600">{{ $format->discountPercent() }}% off</span></span>
                                @endif
                                <span class="mt-1 block text-xs {{ $outOfStock ? 'text-muted' : 'text-leaf-dark' }}">
                                    @if ($format->format->isDigital()) Instant download
                                    @elseif ($outOfStock) Out of stock
                                    @elseif ($format->stock <= \App\Models\BookFormat::LOW_STOCK_THRESHOLD) Only {{ $format->stock }} left
                                    @else In stock @endif
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <button type="button" disabled class="btn-store cursor-not-allowed opacity-60" title="Online ordering is coming soon"><i class="ti ti-shopping-cart"></i> Add to cart</button>
                    @if ($whatsapp = setting('store.whatsapp'))
                        <a href="https://wa.me/{{ preg_replace('/\D+/', '', $whatsapp) }}?text={{ urlencode('Hi! I would like to order "'.$book->title.'".') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-md bg-leaf px-6 py-3 text-sm font-semibold tracking-wide text-white uppercase hover:bg-leaf-dark"><i class="ti ti-brand-whatsapp text-lg"></i> Order on WhatsApp</a>
                    @endif
                </div>
                <p class="mt-3 text-sm text-muted"><i class="ti ti-info-circle"></i> Online ordering with Cash on Delivery is coming soon.</p>
            @endif

            <div class="mt-8 grid gap-3 sm:grid-cols-3">
                @foreach ([['ti-truck-delivery', 'Free delivery above '.money(setting('store.free_shipping_min'))], ['ti-cash', 'Cash on Delivery'], ['ti-certificate', '100% original books']] as [$icon, $text])
                    <div class="flex items-center gap-3 rounded-lg bg-page px-4 py-3 text-sm text-navy"><i class="ti {{ $icon }} text-xl text-primary"></i>{{ $text }}</div>
                @endforeach
            </div>

            @if ($book->description)
                <div class="mt-10">
                    <h2 class="mb-3 font-serif text-2xl text-navy">About this book</h2>
                    <p class="leading-relaxed whitespace-pre-line text-ink">{{ $book->description }}</p>
                </div>
            @endif

            @foreach ($book->authors->filter(fn ($author) => $author->is_active && $author->bio) as $author)
                <div class="mt-10 flex flex-col gap-5 rounded-2xl bg-page p-6 sm:flex-row sm:items-start">
                    <a href="{{ route('authors.show', $author) }}" class="mx-auto size-24 shrink-0 overflow-hidden rounded-full bg-primary-soft ring-4 ring-white sm:mx-0">
                        @if ($author->photoUrl())
                            <img src="{{ $author->photoUrl() }}" alt="{{ $author->name }}" class="size-full object-cover">
                        @else
                            <span class="flex size-full items-center justify-center font-serif text-4xl text-primary">{{ Str::substr($author->name, 0, 1) }}</span>
                        @endif
                    </a>
                    <div>
                        <p class="text-xs font-semibold tracking-[0.2em] text-primary uppercase">About the author</p>
                        <h2 class="mt-1 font-serif text-2xl text-navy">{{ $author->name }}</h2>
                        <p class="mt-2 leading-relaxed text-ink">{{ Str::limit($author->bio, 320) }}</p>
                        <a href="{{ route('authors.show', $author) }}" class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-primary">More about {{ $author->name }} <i class="ti ti-arrow-right"></i></a>
                    </div>
                </div>
            @endforeach

            <div class="mt-10">
                <h2 class="mb-3 font-serif text-2xl text-navy">Details</h2>
                <dl class="grid overflow-hidden rounded-xl border border-line sm:grid-cols-2">
                    @foreach ($details as $label => $value)
                        <div class="flex gap-3 border-b border-line px-4 py-3 text-sm odd:bg-page/50 sm:[&:nth-last-child(-n+2)]:border-b-0">
                            <dt class="w-24 shrink-0 text-muted">{{ $label }}</dt><dd class="font-medium text-navy">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="store-container py-10">
            <x-store.section-heading title="You may also like" :link="route('books.index', ['category' => $book->category?->slug])" />
            <div class="grid grid-cols-2 gap-x-6 gap-y-10 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($related as $item)
                    <x-store.book-card :book="$item" />
                @endforeach
            </div>
        </section>
    @endif
</x-store.layout>
