<x-store.layout :title="$post->title" :description="$post->meta_description ?: $post->summary(155)" :image="$post->coverUrl()">
    <x-store.page-hero title="Blog" :crumbs="['Blog' => route('blog.index'), Str::limit($post->title, 40) => null]" />

    <section class="store-container grid gap-10 py-16 lg:grid-cols-[1fr_320px]">
        <article>
            <p class="mb-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted">
                @if ($post->category)<a href="{{ route('blog.index', ['category' => $post->category]) }}" class="font-semibold tracking-wide text-primary uppercase hover:underline">{{ $post->category }}</a>@endif
                <span><i class="ti ti-calendar"></i> {{ $post->published_at->format('d F Y') }}</span>
                <span><i class="ti ti-clock"></i> {{ $post->readingMinutes() }} min read</span>
                @if ($post->author)<span><i class="ti ti-user"></i> {{ $post->author->name }}</span>@endif
            </p>
            <h1 class="font-serif text-4xl leading-tight font-medium text-navy md:text-5xl">{{ $post->title }}</h1>
            @if ($post->coverUrl())
                <img src="{{ $post->coverUrl() }}" alt="" class="mt-8 aspect-[16/9] w-full rounded-2xl object-cover shadow-soft">
            @endif
            <div class="prose prose-lg mt-10 max-w-none prose-headings:font-serif prose-headings:font-medium prose-headings:text-navy prose-a:text-primary prose-blockquote:border-primary prose-blockquote:text-navy">
                {!! $post->html() !!}
            </div>

            <div class="mt-12 flex flex-wrap items-center gap-3 border-t border-line pt-6">
                <span class="text-sm font-semibold text-navy">Share:</span>
                @php $shareUrl = urlencode(url()->current()); $shareText = urlencode($post->title); @endphp
                <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}" target="_blank" rel="noopener" class="flex size-10 items-center justify-center rounded-full bg-page text-navy hover:bg-leaf hover:text-white" aria-label="Share on WhatsApp"><i class="ti ti-brand-whatsapp text-lg"></i></a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener" class="flex size-10 items-center justify-center rounded-full bg-page text-navy hover:bg-primary hover:text-white" aria-label="Share on Facebook"><i class="ti ti-brand-facebook text-lg"></i></a>
                <a href="https://x.com/intent/post?text={{ $shareText }}&url={{ $shareUrl }}" target="_blank" rel="noopener" class="flex size-10 items-center justify-center rounded-full bg-page text-navy hover:bg-navy hover:text-white" aria-label="Share on X"><i class="ti ti-brand-x text-lg"></i></a>
            </div>

            @if ($related->isNotEmpty())
                <div class="mt-14">
                    <h2 class="mb-6 font-serif text-2xl text-navy">More in {{ $post->category }}</h2>
                    <div class="grid gap-6 md:grid-cols-3">
                        @foreach ($related as $item)
                            <x-store.post-card :post="$item" />
                        @endforeach
                    </div>
                </div>
            @endif
        </article>

        @include('store.blog.sidebar')
    </section>
</x-store.layout>
