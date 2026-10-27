<x-store.layout title="Authors" description="Discover the authors in our store: biographies and every book we stock by them.">
    <x-store.page-hero title="Authors" :crumbs="$letter ? ['Authors' => route('authors.index'), $letter => null] : ['Authors' => null]" />

    <section class="store-container py-14">
        {{-- Search and A–Z --}}
        <div class="mb-10 flex flex-col items-center gap-5">
            <form method="GET" class="flex w-full max-w-lg gap-2">
                <label for="author-search" class="sr-only">Search authors</label>
                <input id="author-search" type="search" name="search" value="{{ request('search') }}" placeholder="Search authors by name" class="store-input">
                <button type="submit" class="btn-store px-5">Search</button>
            </form>
            <nav class="flex flex-wrap justify-center gap-1.5" aria-label="Authors by first letter">
                <a href="{{ route('authors.index') }}" @class(['flex h-9 min-w-9 items-center justify-center rounded-md px-2 text-sm font-semibold', 'bg-primary text-white' => ! $letter, 'bg-page text-navy hover:bg-primary-soft' => $letter])>All</a>
                @foreach (range('A', 'Z') as $char)
                    @if ($letters->contains($char))
                        <a href="{{ route('authors.index', ['letter' => $char]) }}" @class(['flex size-9 items-center justify-center rounded-md text-sm font-semibold', 'bg-primary text-white' => $letter === $char, 'bg-page text-navy hover:bg-primary-soft' => $letter !== $char])>{{ $char }}</a>
                    @else
                        <span class="flex size-9 items-center justify-center rounded-md text-sm text-muted/40">{{ $char }}</span>
                    @endif
                @endforeach
            </nav>
        </div>

        @if ($authors->isEmpty())
            <div class="rounded-xl bg-page p-12 text-center">
                <i class="ti ti-feather mb-3 inline-block text-5xl text-primary"></i>
                <p class="font-serif text-xl text-navy">No authors found</p>
                <a href="{{ route('authors.index') }}" class="btn-store-outline mt-5">Show all authors</a>
            </div>
        @else
            <p class="mb-8 text-center text-muted">{{ $authors->total() }} {{ Str::plural('author', $authors->total()) }}@if (request('search')) matching “<strong class="text-navy">{{ request('search') }}</strong>”@endif</p>
            <div class="grid grid-cols-2 gap-x-6 gap-y-10 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                @foreach ($authors as $author)
                    <x-store.author-card :author="$author" />
                @endforeach
            </div>
            <div class="mt-12">{{ $authors->links() }}</div>
        @endif
    </section>
</x-store.layout>
