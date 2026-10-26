@props(['post'])

<article {{ $attributes->class('group flex flex-col overflow-hidden rounded-xl border border-line bg-white shadow-soft') }}>
    <a href="{{ route('blog.show', $post->slug) }}" class="block aspect-[16/10] overflow-hidden bg-page">
        @if ($post->coverUrl())
            <img src="{{ $post->coverUrl() }}" alt="" loading="lazy" class="size-full object-cover transition duration-500 group-hover:scale-105">
        @endif
    </a>
    <div class="flex flex-1 flex-col p-6">
        <p class="mb-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted">
            @if ($post->category)<a href="{{ route('blog.index', ['category' => $post->category]) }}" class="font-semibold tracking-wide text-primary uppercase hover:underline">{{ $post->category }}</a>@endif
            <span><i class="ti ti-calendar"></i> {{ $post->published_at->format('d M Y') }}</span>
            <span><i class="ti ti-clock"></i> {{ $post->readingMinutes() }} min read</span>
        </p>
        <h3 class="mb-2 font-serif text-xl leading-snug text-navy group-hover:text-primary">
            <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
        </h3>
        <p class="mb-4 line-clamp-3 flex-1 text-sm leading-relaxed text-muted">{{ $post->summary() }}</p>
        <a href="{{ route('blog.show', $post->slug) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary">Read more <i class="ti ti-arrow-right"></i></a>
    </div>
</article>
