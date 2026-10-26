<x-store.layout title="Blog" description="Reading lists, author stories and book news from our team.">
    <x-store.page-hero title="Blog" :crumbs="['Blog' => request('category') ? route('blog.index') : null] + (request('category') ? [request('category') => null] : [])" />

    <section class="store-container grid gap-10 py-16 lg:grid-cols-[1fr_320px]">
        <div>
            @if (request('search') || request('category'))
                <p class="mb-6 text-muted">
                    {{ $posts->total() }} {{ Str::plural('post', $posts->total()) }}
                    @if (request('category')) in <strong class="text-navy">{{ request('category') }}</strong>@endif
                    @if (request('search')) matching “<strong class="text-navy">{{ request('search') }}</strong>”@endif
                    · <a href="{{ route('blog.index') }}" class="text-primary hover:underline">Show all</a>
                </p>
            @endif

            @if ($posts->isEmpty())
                <div class="rounded-xl bg-page p-10 text-center text-muted">No posts found.</div>
            @else
                <div class="grid gap-8 md:grid-cols-2">
                    @foreach ($posts as $post)
                        <x-store.post-card :post="$post" />
                    @endforeach
                </div>
                <div class="mt-10">{{ $posts->links() }}</div>
            @endif
        </div>

        @include('store.blog.sidebar')
    </section>
</x-store.layout>
