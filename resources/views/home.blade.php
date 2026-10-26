<x-store.layout>

    {{-- 1. Hero slider (Marketing → Banners & slider). Without slides, a welcome slide with featured covers. --}}
    @php
        $heroSlides = $slides->isNotEmpty() ? $slides : collect([(object) [
            'eyebrow' => null, 'title' => setting('store.tagline'), 'subtitle' => setting('seo.meta_description'),
            'button_text' => 'Shop now', 'button_link' => route('books.index'), 'image' => null,
        ]]);
        $heroCovers = $featured->filter(fn ($book) => $book->coverUrl())->take(3)->values();
    @endphp
    <section class="relative overflow-hidden" x-data="slider({{ $heroSlides->count() }})" @mouseenter="stop()" @mouseleave="start()">
        @foreach ($heroSlides as $index => $slide)
            @php $image = $slide->image ? Storage::disk('public')->url($slide->image) : null; @endphp
            <div x-show="active === {{ $index }}" @if ($index) x-cloak @endif
                 x-transition:enter="transition duration-700" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 class="relative min-h-[560px] md:min-h-[620px]">
                @if ($image)
                    <img src="{{ $image }}" alt="" class="absolute inset-0 size-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/95 via-white/80 to-white/30"></div>
                @else
                    <div class="absolute inset-0 bg-gradient-to-br from-primary-soft via-white to-amber-50"></div>
                    <div class="absolute -top-24 -left-24 size-96 rounded-full bg-cyan/10"></div>
                    <div class="absolute -right-24 -bottom-32 size-[28rem] rounded-full bg-primary/10"></div>
                @endif

                <div class="store-container relative grid min-h-[560px] items-center gap-10 py-16 md:min-h-[620px] lg:grid-cols-2">
                    <div class="text-center lg:text-left">
                        @if ($slide->eyebrow)
                            <p class="mb-4 text-sm font-semibold tracking-[0.2em] text-primary uppercase">{{ $slide->eyebrow }}</p>
                        @endif
                        <h1 class="font-serif text-4xl leading-tight font-medium text-navy md:text-6xl">{{ $slide->title }}</h1>
                        @if ($slide->subtitle)
                            <p class="mx-auto mt-5 max-w-xl text-lg leading-relaxed text-muted lg:mx-0">{{ $slide->subtitle }}</p>
                        @endif
                        @if ($slide->button_text)
                            <a href="{{ $slide->button_link ? url($slide->button_link) : route('books.index') }}" class="btn-store mt-8">{{ $slide->button_text }} <i class="ti ti-arrow-right"></i></a>
                        @endif
                    </div>

                    @if (! $image && $heroCovers->isNotEmpty())
                        <div class="relative hidden h-[420px] lg:block" aria-hidden="true">
                            @foreach ($heroCovers as $i => $book)
                                <img src="{{ $book->coverUrl() }}" alt="" @class([
                                    'absolute w-52 rounded-md shadow-book',
                                    'top-6 left-10 -rotate-6' => $i === 0,
                                    'top-0 left-1/2 z-10 w-60 -translate-x-1/2' => $i === 1,
                                    'top-10 right-6 rotate-6' => $i === 2,
                                ])>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        @if ($heroSlides->count() > 1)
            <button type="button" @click="prev()" class="absolute top-1/2 left-4 z-10 hidden size-12 -translate-y-1/2 items-center justify-center rounded-full bg-white text-navy shadow-soft hover:bg-primary hover:text-white md:flex" aria-label="Previous slide"><i class="ti ti-chevron-left text-xl"></i></button>
            <button type="button" @click="next()" class="absolute top-1/2 right-4 z-10 hidden size-12 -translate-y-1/2 items-center justify-center rounded-full bg-white text-navy shadow-soft hover:bg-primary hover:text-white md:flex" aria-label="Next slide"><i class="ti ti-chevron-right text-xl"></i></button>
            <div class="absolute bottom-6 left-1/2 z-10 flex -translate-x-1/2 gap-2">
                @foreach ($heroSlides as $index => $slide)
                    <button type="button" @click="go({{ $index }})" class="h-2.5 rounded-full transition-all" :class="active === {{ $index }} ? 'w-8 bg-primary' : 'w-2.5 bg-navy/25'" aria-label="Slide {{ $index + 1 }}"></button>
                @endforeach
            </div>
        @endif
    </section>

    {{-- 2. Categories --}}
    @if (setting('home.show_categories') && $categories->isNotEmpty())
        <section class="store-container py-16">
            <x-store.section-heading :title="setting('home.categories_title')" :link="route('books.index')" />
            @php $categoryIcons = ['ti-book', 'ti-heart', 'ti-search', 'ti-wand', 'ti-rocket', 'ti-flag', 'ti-bulb', 'ti-user-star', 'ti-yin-yang', 'ti-school', 'ti-balloon', 'ti-language-hiragana', 'ti-certificate']; @endphp
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach ($categories as $i => $category)
                    <a href="{{ route('books.index', ['category' => $category->slug]) }}"
                       class="group flex items-center gap-4 rounded-xl border border-line bg-white p-4 transition hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-soft">
                        <span class="flex size-12 shrink-0 items-center justify-center rounded-lg bg-primary-soft text-primary transition group-hover:bg-primary group-hover:text-white">
                            <i class="ti {{ $categoryIcons[$i % count($categoryIcons)] }} text-2xl"></i>
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate font-semibold text-navy">{{ $category->name }}</span>
                            <span class="text-sm text-muted">{{ $category->books_count }} {{ Str::plural('book', $category->books_count) }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- 3. Bestsellers shelf --}}
    @if (setting('home.show_bestsellers') && $bestsellers->isNotEmpty())
        <section class="bg-page py-16" x-data="shelf">
            <div class="store-container">
                <x-store.section-heading :title="setting('home.bestsellers_title')" :link="route('books.index', ['show' => 'bestsellers'])">
                    <button type="button" @click="scroll(-1)" class="flex size-10 items-center justify-center rounded-full border border-line bg-white text-navy hover:bg-primary hover:text-white" aria-label="Scroll left"><i class="ti ti-chevron-left"></i></button>
                    <button type="button" @click="scroll(1)" class="flex size-10 items-center justify-center rounded-full border border-line bg-white text-navy hover:bg-primary hover:text-white" aria-label="Scroll right"><i class="ti ti-chevron-right"></i></button>
                </x-store.section-heading>
                <div x-ref="track" class="no-scrollbar -mx-2 flex snap-x gap-6 overflow-x-auto px-2 pt-2 pb-4">
                    @foreach ($bestsellers as $book)
                        <x-store.book-card :book="$book" class="w-40 shrink-0 snap-start sm:w-48" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- 4. New arrivals --}}
    @if (setting('home.show_new_arrivals') && $newArrivals->isNotEmpty())
        <section class="store-container py-16">
            <x-store.section-heading :title="setting('home.new_arrivals_title')" :link="route('books.index')" />
            <div class="grid grid-cols-2 gap-x-6 gap-y-10 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($newArrivals as $book)
                    <x-store.book-card :book="$book" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- 5. eBooks promotion --}}
    @if (setting('home.show_ebooks') && $ebooks->isNotEmpty())
        <section class="store-container py-10">
            <div class="grid items-center gap-10 overflow-hidden rounded-2xl bg-gradient-to-br from-amber-50 via-white to-primary-soft p-8 md:p-12 lg:grid-cols-2">
                <div>
                    <p class="mb-3 text-sm font-semibold tracking-[0.2em] text-primary uppercase">{{ setting('home.ebooks_eyebrow') }}</p>
                    <h2 class="font-serif text-4xl leading-tight font-medium text-navy md:text-5xl">{{ setting('home.ebooks_title') }}</h2>
                    <p class="mt-4 max-w-lg leading-relaxed text-muted">{{ setting('home.ebooks_text') }}</p>
                    <a href="{{ route('books.index', ['format' => 'ebook']) }}" class="btn-store mt-7">{{ setting('home.ebooks_button') }} <i class="ti ti-device-tablet"></i></a>
                </div>
                <div class="space-y-4">
                    @foreach ($ebooks as $book)
                        @php $ebook = $book->formats->first(fn ($f) => $f->format->isDigital()); @endphp
                        <a href="{{ route('books.show', $book) }}" class="flex items-center gap-5 rounded-xl bg-white p-4 shadow-soft transition hover:-translate-y-0.5">
                            @if ($book->coverUrl())
                                <img src="{{ $book->coverUrl() }}" alt="" class="h-32 w-24 shrink-0 rounded object-cover shadow-book">
                            @endif
                            <div class="min-w-0">
                                <span class="mb-2 inline-block rounded bg-leaf px-2 py-0.5 text-[11px] font-bold tracking-wide text-white uppercase">eBook</span>
                                <h3 class="font-serif text-xl text-navy">{{ $book->title }}</h3>
                                <p class="text-sm text-muted">{{ $book->authors->pluck('name')->join(', ') }}</p>
                                <p class="mt-2 text-sm font-semibold text-primary">{{ money($ebook->price) }} <span class="ml-1 font-normal text-muted">· Read instantly <i class="ti ti-player-play"></i></span></p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- 6. Two category columns + offer box --}}
    @if (setting('home.show_category_columns') && $columns->isNotEmpty())
        <section class="store-container py-16">
            <div @class(['grid gap-10', 'lg:grid-cols-[1fr_1fr_320px]' => $promo, 'lg:grid-cols-2' => ! $promo])>
                @foreach ($columns as $column)
                    <div>
                        <div class="mb-6 flex items-center justify-between border-b border-line pb-3">
                            <h2 class="font-serif text-2xl text-navy md:text-3xl">{{ $column['category']->name }}</h2>
                            <a href="{{ route('books.index', ['category' => $column['category']->slug]) }}" class="text-sm font-semibold text-primary hover:underline">View all</a>
                        </div>
                        <div class="grid grid-cols-2 gap-x-5 gap-y-8">
                            @foreach ($column['books'] as $book)
                                <x-store.book-card :book="$book" size="sm" />
                            @endforeach
                        </div>
                    </div>
                @endforeach

                @if ($promo)
                    <aside class="relative flex flex-col overflow-hidden rounded-2xl bg-gradient-to-b from-navy to-primary text-center text-white">
                        @if ($promo->imageUrl())
                            <img src="{{ $promo->imageUrl() }}" alt="" class="h-64 w-full object-cover">
                        @else
                            <div class="flex h-56 items-end justify-center gap-3 overflow-hidden pt-8" aria-hidden="true">
                                @foreach ($featured->filter(fn ($b) => $b->coverUrl())->take(2) as $i => $book)
                                    <img src="{{ $book->coverUrl() }}" alt="" @class(['w-28 rounded shadow-book', '-rotate-6 translate-y-4' => $i === 0, 'rotate-6' => $i === 1])>
                                @endforeach
                            </div>
                        @endif
                        <div class="flex flex-1 flex-col justify-center p-8">
                            @if ($promo->eyebrow)<p class="mb-2 font-serif text-lg tracking-wide uppercase">{{ $promo->eyebrow }}</p>@endif
                            <p class="font-serif text-5xl font-semibold text-amber-300">{{ $promo->title }}</p>
                            @if ($promo->subtitle)<p class="mt-3 text-sm text-white/80">{{ $promo->subtitle }}</p>@endif
                            @if ($promo->button_text)
                                <a href="{{ url($promo->button_link ?: '/books') }}" class="mt-6 inline-flex items-center justify-center rounded-md bg-white px-6 py-3 text-sm font-bold tracking-wide text-primary uppercase hover:bg-primary-soft">{{ $promo->button_text }}</a>
                            @endif
                        </div>
                    </aside>
                @endif
            </div>
        </section>
    @endif

    {{-- 7. Book spotlight --}}
    @if (setting('home.show_spotlight') && $spotlight)
        @php $spotFormat = $spotlight->formats->where('is_active', true)->sortBy('price')->first(); $author = $spotlight->authors->first(); @endphp
        <section class="store-container py-10">
            <div class="grid items-center overflow-hidden rounded-2xl bg-gradient-to-br from-primary-soft via-white to-amber-50 lg:grid-cols-2">
                <div class="p-8 md:p-14">
                    <p class="mb-4 text-sm font-semibold tracking-[0.2em] text-muted uppercase">{{ setting('home.spotlight_eyebrow') }}</p>
                    <h2 class="font-serif text-4xl leading-tight font-medium text-navy">{{ $spotlight->title }}</h2>
                    <p class="mt-5 font-serif text-xl text-amber-600">{{ setting('home.spotlight_label') }}@if ($spotFormat) · {{ money($spotFormat->price) }}@endif</p>
                    <p class="mt-4 leading-relaxed text-muted">{{ setting('home.spotlight_text') ?: Str::limit($spotlight->description, 260) }}</p>
                    <a href="{{ route('books.show', $spotlight) }}" class="btn-store mt-7">{{ setting('home.spotlight_button') }}</a>
                    @if ($author)
                        <div class="mt-10 flex items-center gap-4">
                            <span class="flex size-16 items-center justify-center rounded-xl bg-white font-serif text-2xl text-primary shadow-soft">{{ Str::substr($author->name, 0, 1) }}</span>
                            <div>
                                <p class="font-serif text-lg text-navy">{{ $author->name }}</p>
                                <p class="text-sm text-muted">{{ $author->books()->count() > 1 ? $author->books()->count().' books in our store' : 'Author' }}</p>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="flex items-center justify-center p-10 lg:p-16">
                    @if ($spotlight->coverUrl())
                        <img src="{{ $spotlight->coverUrl() }}" alt="{{ $spotlight->title }} cover" class="w-64 rotate-3 rounded-md shadow-book md:w-80">
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- 8. Testimonials --}}
    @if (setting('home.show_testimonials'))
        <x-store.testimonials :testimonials="$testimonials" :title="setting('home.testimonials_title')" />
    @endif

    {{-- 9. Blog --}}
    @if (setting('home.show_blog') && $posts->isNotEmpty())
        <section class="store-container py-16">
            <x-store.section-heading :title="setting('home.blog_title')" :link="route('blog.index')" link-text="View all posts" />
            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($posts as $post)
                    <x-store.post-card :post="$post" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- 10. Newsletter --}}
    @if (setting('home.show_newsletter'))
        <x-store.newsletter />
    @endif
</x-store.layout>
