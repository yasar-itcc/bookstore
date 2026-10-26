@use('App\Models\Setting')

@php
    $introImage = Setting::imageUrl('about.intro_image');
    $quoteImage = Setting::imageUrl('about.quote_image');
    $paragraphs = preg_split('/\n\s*\n/', trim((string) setting('about.intro_text')));
@endphp

<x-store.layout :title="setting('about.hero_title')" :description="Str::limit(strip_tags(setting('about.intro_text')), 155)">
    <x-store.page-hero :title="setting('about.hero_title')" :crumbs="[setting('about.hero_title') => null]" />

    {{-- Our story: image left, text right --}}
    <section class="store-container grid items-center gap-12 py-20 lg:grid-cols-2">
        <div>
            @if ($introImage)
                <img src="{{ $introImage }}" alt="" class="aspect-[4/3] w-full rounded-2xl object-cover shadow-soft">
            @else
                {{-- Cover collage from the catalog --}}
                <div class="relative grid grid-cols-3 gap-4 rounded-2xl bg-gradient-to-br from-primary-soft to-amber-50 p-8" aria-hidden="true">
                    @foreach ($covers->take(6) as $i => $book)
                        <img src="{{ $book->coverUrl() }}" alt="" @class(['w-full rounded shadow-book', 'translate-y-6' => $i % 3 === 1])>
                    @endforeach
                </div>
            @endif
        </div>
        <div>
            <p class="mb-3 text-sm font-semibold tracking-[0.2em] text-muted uppercase">{{ setting('about.intro_eyebrow') }}</p>
            <h2 class="font-serif text-4xl leading-tight font-medium text-navy">{{ setting('about.intro_title') }}</h2>
            <div class="mt-5 space-y-4 leading-relaxed text-muted">
                @foreach ($paragraphs as $paragraph)<p>{{ $paragraph }}</p>@endforeach
            </div>
            @if (setting('about.intro_button'))
                <a href="{{ route('books.index') }}" class="btn-store mt-7">{{ setting('about.intro_button') }}</a>
            @endif
        </div>
    </section>

    {{-- Quote: text left, image right --}}
    <section class="store-container grid items-center gap-12 pb-20 lg:grid-cols-2">
        <div class="order-2 lg:order-1">
            <i class="ti ti-quote mb-2 block text-5xl text-primary/30"></i>
            <blockquote class="font-serif text-3xl leading-snug text-navy italic">“{{ setting('about.quote') }}”</blockquote>
            @if (setting('about.quote_author'))<p class="mt-3 font-semibold text-primary">— {{ setting('about.quote_author') }}</p>@endif
            <p class="mt-5 leading-relaxed text-muted">{{ setting('about.quote_text') }}</p>
            @if (setting('about.quote_button'))
                <a href="{{ route('blog.index') }}" class="btn-store mt-7">{{ setting('about.quote_button') }}</a>
            @endif
        </div>
        <div class="order-1 lg:order-2">
            @if ($quoteImage)
                <img src="{{ $quoteImage }}" alt="" class="aspect-[4/3] w-full rounded-2xl object-cover shadow-soft">
            @else
                <div class="relative flex aspect-[4/3] items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-navy to-primary" aria-hidden="true">
                    <div class="absolute -top-10 -left-10 size-48 rounded-full bg-cyan/20"></div>
                    <div class="absolute -right-12 -bottom-12 size-56 rounded-full bg-leaf/20"></div>
                    @foreach ($covers->slice(2, 3)->values() as $i => $book)
                        <img src="{{ $book->coverUrl() }}" alt="" @class(['relative w-32 rounded shadow-book md:w-40', '-mr-8 -rotate-6' => $i === 0, 'z-10 scale-110' => $i === 1, '-ml-8 rotate-6' => $i === 2])>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Why shop with us --}}
    @if ($features = Setting::json('about.features'))
        <section class="store-container pb-20">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($features as $feature)
                    <div class="flex items-center gap-4 rounded-xl bg-primary-soft/60 p-5">
                        <span class="flex size-12 shrink-0 items-center justify-center rounded-lg bg-white text-primary shadow-sm"><i class="ti {{ $feature['icon'] }} text-2xl"></i></span>
                        <div>
                            <p class="font-semibold text-navy">{{ $feature['title'] }}</p>
                            <p class="text-sm text-muted">{{ $feature['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Team --}}
    @if ($team = Setting::json('about.team'))
        <section class="bg-page py-20">
            <div class="store-container">
                <h2 class="section-title mb-10 text-center">{{ setting('about.team_title') }}</h2>
                <div class="mx-auto grid max-w-5xl gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($team as $member)
                        <div class="overflow-hidden rounded-2xl bg-white text-center shadow-soft">
                            @if (! empty($member['photo']))
                                <img src="{{ Storage::disk('public')->url($member['photo']) }}" alt="{{ $member['name'] }}" class="aspect-square w-full object-cover">
                            @else
                                <div class="flex aspect-square items-center justify-center bg-gradient-to-br from-primary-soft to-cyan-soft font-serif text-7xl text-primary">{{ Str::substr($member['name'], 0, 1) }}</div>
                            @endif
                            <div class="p-5">
                                <p class="font-serif text-xl text-navy">{{ $member['name'] }}</p>
                                <p class="text-sm text-muted">{{ $member['role'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-store.testimonials :testimonials="$testimonials" :title="setting('home.testimonials_title')" />
    <x-store.newsletter />
</x-store.layout>
