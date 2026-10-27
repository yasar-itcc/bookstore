@php $paragraphs = preg_split('/\n\s*\n/', trim((string) $author->bio)); @endphp

<x-store.layout :title="$author->name" :description="Str::limit($author->bio, 155) ?: 'Books by '.$author->name" :image="$author->photoUrl()">
    <x-store.page-hero :title="$author->name" :crumbs="['Authors' => route('authors.index'), $author->name => null]" />

    {{-- Photo and biography --}}
    <section class="store-container py-14">
        <div class="grid items-center gap-10 rounded-2xl bg-gradient-to-br from-primary-soft via-white to-amber-50 p-8 md:grid-cols-[280px_1fr] md:p-12">
            <div class="mx-auto aspect-square w-56 overflow-hidden rounded-full bg-white shadow-book ring-8 ring-white md:w-full">
                @if ($author->photoUrl())
                    <img src="{{ $author->photoUrl() }}" alt="{{ $author->name }}" class="size-full object-cover">
                @else
                    <span class="flex size-full items-center justify-center font-serif text-8xl text-primary">{{ Str::substr($author->name, 0, 1) }}</span>
                @endif
            </div>
            <div>
                <p class="mb-2 text-sm font-semibold tracking-[0.2em] text-primary uppercase">About the author</p>
                <h2 class="font-serif text-4xl font-medium text-navy">{{ $author->name }}</h2>
                <div class="mt-5 space-y-4 text-lg leading-relaxed text-ink">
                    @forelse (array_filter($paragraphs) as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @empty
                        <p class="text-muted">Browse every book by {{ $author->name }} in our store below.</p>
                    @endforelse
                </div>
                <div class="mt-6 flex flex-wrap gap-3 text-sm">
                    <span class="rounded-full bg-white px-4 py-1.5 font-medium text-navy shadow-sm"><i class="ti ti-books text-primary"></i> {{ $books->count() }} {{ Str::plural('book', $books->count()) }} in store</span>
                    @foreach ($books->pluck('category')->filter()->unique('id')->take(3) as $category)
                        <a href="{{ route('books.index', ['category' => $category->slug]) }}" class="rounded-full bg-white px-4 py-1.5 text-muted shadow-sm hover:text-primary">{{ $category->name }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Books --}}
    <section class="store-container py-6">
        <x-store.section-heading :title="'Books by '.$author->name" />
        @if ($books->isEmpty())
            <p class="rounded-xl bg-page p-8 text-center text-muted">No books by this author are available right now.</p>
        @else
            <div class="grid grid-cols-2 gap-x-6 gap-y-10 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($books as $book)
                    <x-store.book-card :book="$book" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- Related authors --}}
    @if ($related->isNotEmpty())
        <section class="store-container py-14">
            <x-store.section-heading title="Readers also enjoy" :link="route('authors.index')" link-text="All authors" />
            <div class="grid grid-cols-2 gap-x-6 gap-y-10 sm:grid-cols-3 lg:grid-cols-6">
                @foreach ($related as $item)
                    <x-store.author-card :author="$item" />
                @endforeach
            </div>
        </section>
    @endif
</x-store.layout>
