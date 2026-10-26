@use('App\Enums\BookFormat', 'Format')

@php
    $heading = $category?->name ?? match (request('show')) { 'bestsellers' => 'Bestsellers', 'featured' => 'Featured books', default => (request('format') === 'ebook' ? 'eBooks' : 'All books') };
@endphp

<x-store.layout :title="$heading" :description="$category?->description">
    <x-store.page-hero :title="$heading" :crumbs="$category ? ['Books' => route('books.index'), $category->name => null] : ['Books' => null]" />

    <section class="store-container grid gap-10 py-14 lg:grid-cols-[260px_1fr]">
        {{-- Filters --}}
        <aside class="space-y-6">
            <form method="GET" class="rounded-xl bg-page p-5">
                @foreach (request()->only(['category', 'format', 'show', 'sort']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <label for="book-search" class="mb-2 block font-serif text-lg text-navy">Search</label>
                <div class="flex gap-2">
                    <input id="book-search" type="search" name="search" value="{{ request('search') }}" placeholder="Title, author, ISBN" class="store-input py-2.5">
                    <button type="submit" class="flex w-11 shrink-0 items-center justify-center rounded-md bg-primary text-white" aria-label="Search"><i class="ti ti-search"></i></button>
                </div>
            </form>

            <div class="rounded-xl bg-page p-5">
                <p class="mb-3 font-serif text-lg text-navy">Categories</p>
                <ul class="space-y-1 text-sm">
                    <li><a href="{{ route('books.index', request()->except(['category', 'page'])) }}" @class(['block rounded px-2 py-1.5', 'bg-primary text-white' => ! $category, 'hover:text-primary' => $category])>All books</a></li>
                    @foreach ($categories as $parent)
                        <li>
                            <a href="{{ route('books.index', ['category' => $parent->slug] + request()->except(['category', 'page'])) }}" @class(['block rounded px-2 py-1.5 font-medium', 'bg-primary text-white' => $category?->is($parent), 'text-navy hover:text-primary' => ! $category?->is($parent)])>{{ $parent->name }}</a>
                            @if ($parent->children->isNotEmpty())
                                <ul class="ml-3 border-l border-line pl-2">
                                    @foreach ($parent->children as $child)
                                        <li><a href="{{ route('books.index', ['category' => $child->slug] + request()->except(['category', 'page'])) }}" @class(['block rounded px-2 py-1', 'bg-primary text-white' => $category?->is($child), 'text-muted hover:text-primary' => ! $category?->is($child)])>{{ $child->name }}</a></li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="rounded-xl bg-page p-5">
                <p class="mb-3 font-serif text-lg text-navy">Format</p>
                <div class="flex flex-wrap gap-2 text-sm">
                    <a href="{{ route('books.index', request()->except(['format', 'page'])) }}" @class(['rounded-full border px-3 py-1', 'border-primary bg-primary text-white' => ! request('format'), 'border-line bg-white' => request('format')])>Any</a>
                    @foreach (Format::cases() as $format)
                        <a href="{{ route('books.index', ['format' => $format->value] + request()->except(['format', 'page'])) }}" @class(['rounded-full border px-3 py-1', 'border-primary bg-primary text-white' => request('format') === $format->value, 'border-line bg-white' => request('format') !== $format->value])>{{ $format->label() }}</a>
                    @endforeach
                </div>
            </div>
        </aside>

        {{-- Results --}}
        <div>
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <p class="text-muted">{{ $books->total() }} {{ Str::plural('book', $books->total()) }}@if (request('search')) for “<strong class="text-navy">{{ request('search') }}</strong>”@endif</p>
                <form method="GET" class="flex items-center gap-2 text-sm">
                    @foreach (request()->except(['sort', 'page']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <label for="sort" class="text-muted">Sort by</label>
                    <select id="sort" name="sort" onchange="this.form.submit()" class="store-input w-auto py-2">
                        @foreach (['' => 'Newest', 'price_low' => 'Price: low to high', 'price_high' => 'Price: high to low', 'title' => 'Title A–Z'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('sort', '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            @if ($books->isEmpty())
                <div class="rounded-xl bg-page p-12 text-center">
                    <i class="ti ti-books mb-3 inline-block text-5xl text-primary"></i>
                    <p class="font-serif text-xl text-navy">No books found</p>
                    <p class="mt-1 text-muted">Try a different search or category.</p>
                    <a href="{{ route('books.index') }}" class="btn-store-outline mt-5">Show all books</a>
                </div>
            @else
                <div class="grid grid-cols-2 gap-x-6 gap-y-10 sm:grid-cols-3 xl:grid-cols-4">
                    @foreach ($books as $book)
                        <x-store.book-card :book="$book" />
                    @endforeach
                </div>
                <div class="mt-12">{{ $books->links() }}</div>
            @endif
        </div>
    </section>
</x-store.layout>
