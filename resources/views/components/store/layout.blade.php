@props(['title' => null, 'description' => null, 'image' => null])

@php
    $storeName = setting('store.name');
    $pageTitle = $title ? "{$title} | {$storeName}" : setting('seo.meta_title');
    $metaDescription = $description ?: setting('seo.meta_description');
    $shareImage = $image ?: \App\Models\Setting::imageUrl('seo.og_image');
    $logo = \App\Models\Setting::imageUrl('store.logo');
    $logoLight = \App\Models\Setting::imageUrl('store.logo_light');
    $favicon = \App\Models\Setting::imageUrl('store.favicon');

    $nav = [
        ['Home', route('home'), 'home'],
        ['Books', route('books.index'), 'books.*'],
        ['Authors', route('authors.index'), 'authors.*'],
        ['About', route('about'), 'about'],
        ['Pricing', route('pricing'), 'pricing'],
        ['Blog', route('blog.index'), 'blog.*'],
        ['Contact', route('contact'), 'contact'],
    ];
    $footerPages = \App\Models\Page::where('is_published', true)->where('show_in_footer', true)->orderBy('title')->get(['title', 'slug']);
    $footerCategories = \App\Models\Category::where('is_active', true)->has('books')->withCount('books')->orderByDesc('books_count')->take(6)->get(['id', 'name', 'slug']);
    $socials = array_filter([
        'ti-brand-facebook' => setting('store.facebook'),
        'ti-brand-instagram' => setting('store.instagram'),
        'ti-brand-x' => setting('store.x'),
        'ti-brand-youtube' => setting('store.youtube'),
    ]);
@endphp

<!DOCTYPE html>
<html lang="en-IN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    @if (setting('seo.meta_keywords'))<meta name="keywords" content="{{ setting('seo.meta_keywords') }}">@endif
    @unless (setting('seo.allow_indexing'))<meta name="robots" content="noindex, nofollow">@endunless
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $storeName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($shareImage)<meta property="og:image" content="{{ $shareImage }}">@endif
    @if ($favicon)<link rel="icon" href="{{ $favicon }}">@endif
    @fonts(['public-sans', 'lora'])
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if ($gaId = setting('seo.google_analytics_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($gaId) }}"></script>
        <script>window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', @js($gaId));</script>
    @endif
</head>
<body class="bg-white font-sans text-ink antialiased" x-data="{ menuOpen: false, searchOpen: false }">

    {{-- Announcement bar --}}
    @if (setting('home.announcement_enabled') && setting('home.announcement_text'))
        <div class="bg-primary text-white">
            <div class="store-container flex flex-wrap items-center justify-center gap-x-4 gap-y-1 py-2 text-center text-sm">
                <span><i class="ti ti-truck-delivery mr-1"></i>{{ setting('home.announcement_text') }}</span>
                @if (setting('home.announcement_link'))
                    <a href="{{ url(setting('home.announcement_link')) }}" class="rounded bg-white px-3 py-0.5 text-xs font-bold tracking-wide text-primary uppercase hover:bg-primary-soft">Shop now</a>
                @endif
            </div>
        </div>
    @endif

    {{-- Header --}}
    <header class="sticky top-0 z-40 border-b border-line/60 bg-white/95 backdrop-blur">
        <div class="store-container flex h-20 items-center gap-6 md:h-24">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5" aria-label="{{ $storeName }} home">
                @if ($logo)
                    <img src="{{ $logo }}" alt="{{ $storeName }}" class="h-12 w-auto max-w-44 object-contain md:h-16 md:max-w-56">
                @else
                    <span class="flex size-10 items-center justify-center rounded-lg bg-primary text-white"><i class="ti ti-book text-2xl"></i></span>
                    <span class="text-2xl font-bold tracking-tight text-navy">{{ $storeName }}</span>
                @endif
            </a>

            <nav class="ml-auto hidden items-center gap-1 lg:flex" aria-label="Main">
                @foreach ($nav as [$label, $href, $pattern])
                    <a href="{{ $href }}" @class([
                        'rounded-md px-3.5 py-2 text-[15px] font-medium transition',
                        'text-primary' => request()->routeIs($pattern),
                        'text-navy hover:text-primary' => ! request()->routeIs($pattern),
                    ])>{{ $label }}</a>
                @endforeach
            </nav>

            <div class="ml-auto flex items-center gap-2 lg:ml-4">
                <button type="button" @click="searchOpen = !searchOpen" class="flex size-10 items-center justify-center rounded-full text-navy hover:bg-page" aria-label="Search books">
                    <i class="ti ti-search text-xl"></i>
                </button>
                <a href="{{ route('books.index') }}" class="relative flex h-11 items-center gap-2 rounded-md bg-primary px-4 text-white shadow-md shadow-primary/30 hover:bg-primary-dark" aria-label="Cart">
                    <i class="ti ti-shopping-cart text-xl"></i>
                    <span class="hidden text-sm font-semibold sm:inline">Cart</span>
                </a>
                <button type="button" @click="menuOpen = !menuOpen" class="flex size-10 items-center justify-center rounded-md bg-page text-navy lg:hidden" aria-label="Menu">
                    <i class="ti text-2xl" :class="menuOpen ? 'ti-x' : 'ti-menu-2'"></i>
                </button>
            </div>
        </div>

        {{-- Search --}}
        <div x-cloak x-show="searchOpen" x-transition @keydown.escape.window="searchOpen = false" class="border-t border-line bg-white">
            <form action="{{ route('books.index') }}" method="GET" class="store-container flex gap-3 py-4">
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by title, author or ISBN" class="store-input" x-ref="search" x-init="$watch('searchOpen', v => v && $nextTick(() => $refs.search.focus()))">
                <button type="submit" class="btn-store">Search</button>
            </form>
        </div>

        {{-- Mobile menu --}}
        <nav x-cloak x-show="menuOpen" x-transition class="border-t border-line bg-white lg:hidden" aria-label="Mobile">
            <div class="store-container flex flex-col py-3">
                @foreach ($nav as [$label, $href, $pattern])
                    <a href="{{ $href }}" @class(['rounded-md px-3 py-3 font-medium', 'bg-primary-soft text-primary' => request()->routeIs($pattern), 'text-navy' => ! request()->routeIs($pattern)])>{{ $label }}</a>
                @endforeach
            </div>
        </nav>
    </header>

    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="mt-20 bg-navy text-white/80">
        <div class="store-container grid gap-10 py-16 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
            <div>
                <a href="{{ route('home') }}" class="mb-5 inline-flex items-center gap-2.5" aria-label="{{ $storeName }} home">
                    @if ($logoLight)
                        <img src="{{ $logoLight }}" alt="{{ $storeName }}" class="h-16 w-auto max-w-56 object-contain">
                    @elseif ($logo)
                        {{-- The main logo is made for light backgrounds, so it sits on a white badge here. --}}
                        <span class="inline-flex rounded-xl bg-white px-4 py-3 shadow-sm">
                            <img src="{{ $logo }}" alt="{{ $storeName }}" class="h-14 w-auto max-w-52 object-contain">
                        </span>
                    @else
                        <span class="flex size-10 items-center justify-center rounded-lg bg-white/10 text-cyan"><i class="ti ti-book text-2xl"></i></span>
                        <span class="text-2xl font-bold text-white">{{ $storeName }}</span>
                    @endif
                </a>
                <p class="mb-5 text-sm leading-relaxed">{{ setting('store.footer_about') }}</p>
                @if ($socials)
                    <p class="mb-3 font-serif text-lg text-white">Follow us</p>
                    <div class="flex gap-2">
                        @foreach ($socials as $icon => $link)
                            <a href="{{ $link }}" target="_blank" rel="noopener" class="flex size-10 items-center justify-center rounded-full bg-white/10 text-white hover:bg-primary" aria-label="{{ str($icon)->after('ti-brand-')->title() }}"><i class="ti {{ $icon }} text-lg"></i></a>
                        @endforeach
                    </div>
                @endif
            </div>
            <div>
                <p class="mb-4 font-serif text-lg text-white">Menu</p>
                <ul class="space-y-2.5 text-sm">
                    @foreach ($nav as [$label, $href])
                        <li><a href="{{ $href }}" class="hover:text-cyan">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <p class="mb-4 font-serif text-lg text-white">Help</p>
                <ul class="space-y-2.5 text-sm">
                    @foreach ($footerPages as $footerPage)
                        <li><a href="{{ route('pages.show', $footerPage->slug) }}" class="hover:text-cyan">{{ $footerPage->title }}</a></li>
                    @endforeach
                    <li><a href="{{ route('contact') }}" class="hover:text-cyan">Contact us</a></li>
                </ul>
            </div>
            <div>
                <p class="mb-4 font-serif text-lg text-white">Categories</p>
                <ul class="space-y-2.5 text-sm">
                    @foreach ($footerCategories as $footerCategory)
                        <li><a href="{{ route('books.index', ['category' => $footerCategory->slug]) }}" class="hover:text-cyan">{{ $footerCategory->name }}</a></li>
                    @endforeach
                </ul>
                <div class="mt-6 space-y-2 text-sm">
                    @if (setting('store.phone'))<p><i class="ti ti-phone mr-2 text-cyan"></i><a href="tel:{{ preg_replace('/\s+/', '', setting('store.phone')) }}" class="hover:text-cyan">{{ setting('store.phone') }}</a></p>@endif
                    @if (setting('store.email'))<p><i class="ti ti-mail mr-2 text-cyan"></i><a href="mailto:{{ setting('store.email') }}" class="hover:text-cyan">{{ setting('store.email') }}</a></p>@endif
                </div>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="store-container flex flex-wrap items-center justify-between gap-3 py-5 text-sm">
                <p>© {{ date('Y') }} {{ $storeName }}. All rights reserved.</p>
                <p class="flex items-center gap-2"><i class="ti ti-cash text-cyan"></i> Cash on Delivery available across India</p>
            </div>
        </div>
    </footer>

    @if ($whatsapp = setting('store.whatsapp'))
        <a href="https://wa.me/{{ preg_replace('/\D+/', '', $whatsapp) }}" target="_blank" rel="noopener"
           class="fixed right-5 bottom-5 z-40 flex size-14 items-center justify-center rounded-full bg-leaf text-white shadow-lg hover:bg-leaf-dark" aria-label="Chat on WhatsApp">
            <i class="ti ti-brand-whatsapp text-3xl"></i>
        </a>
    @endif
</body>
</html>
