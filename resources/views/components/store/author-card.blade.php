@props(['author'])

<a href="{{ route('authors.show', $author) }}" {{ $attributes->class('group block text-center') }}>
    <div class="mx-auto mb-4 aspect-square w-full max-w-44 overflow-hidden rounded-full bg-primary-soft shadow-soft ring-4 ring-white transition duration-300 group-hover:-translate-y-1 group-hover:ring-primary/30">
        @if ($author->photoUrl())
            <img src="{{ $author->photoUrl() }}" alt="{{ $author->name }}" loading="lazy" class="size-full object-cover">
        @else
            <span class="flex size-full items-center justify-center font-serif text-5xl text-primary">{{ Str::substr($author->name, 0, 1) }}</span>
        @endif
    </div>
    <h3 class="font-serif text-lg leading-snug text-navy group-hover:text-primary">{{ $author->name }}</h3>
    @isset($author->books_count)
        <p class="text-sm text-muted">{{ $author->books_count }} {{ Str::plural('book', $author->books_count) }}</p>
    @endisset
</a>
