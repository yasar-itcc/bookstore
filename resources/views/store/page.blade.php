<x-store.layout :title="$page->title" :description="$page->meta_description">
    <x-store.page-hero :title="$page->title" :crumbs="[$page->title => null]" />

    <article class="store-container py-16">
        <div class="prose prose-lg mx-auto max-w-3xl prose-headings:font-serif prose-headings:font-medium prose-headings:text-navy prose-a:text-primary">
            {!! $page->html() !!}
        </div>
        <p class="mx-auto mt-12 max-w-3xl border-t border-line pt-6 text-sm text-muted">Last updated {{ $page->updated_at->format('d F Y') }}. Questions? <a href="{{ route('contact') }}" class="text-primary hover:underline">Contact us</a>.</p>
    </article>
</x-store.layout>
