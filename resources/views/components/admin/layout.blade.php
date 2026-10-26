@props(['title' => 'Dashboard'])

@php
    // Sidebar menu. Each section has a heading (like Vuexy's "Apps & Pages") and its links.
    $user = auth()->user();
    $storeName = setting('store.name');
    $logo = \App\Models\Setting::imageUrl('store.logo');
    $unreadMessages = $user->canAccessSection('messages') ? \App\Models\ContactMessage::whereNull('read_at')->count() : 0;
    $menu = [
        null => [
            ['label' => 'Dashboard', 'icon' => 'ti-smart-home', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'section' => 'dashboard'],
        ],
        'Catalog' => [
            ['label' => 'Books', 'icon' => 'ti-books', 'route' => 'admin.books.index', 'active' => 'admin.books.*', 'section' => 'catalog'],
            ['label' => 'Categories', 'icon' => 'ti-category', 'route' => 'admin.categories.index', 'active' => 'admin.categories.*', 'section' => 'catalog'],
            ['label' => 'Authors', 'icon' => 'ti-feather', 'route' => 'admin.authors.index', 'active' => 'admin.authors.*', 'section' => 'catalog'],
            ['label' => 'Publishers', 'icon' => 'ti-building-store', 'route' => 'admin.publishers.index', 'active' => 'admin.publishers.*', 'section' => 'catalog'],
            ['label' => 'Languages', 'icon' => 'ti-language', 'route' => 'admin.languages.index', 'active' => 'admin.languages.*', 'section' => 'catalog'],
        ],
        'Marketing' => [
            ['label' => 'Coupons', 'icon' => 'ti-discount-2', 'route' => 'admin.coupons.index', 'active' => 'admin.coupons.*', 'section' => 'marketing'],
            ['label' => 'Banners & slider', 'icon' => 'ti-photo', 'route' => 'admin.banners.index', 'active' => 'admin.banners.*', 'section' => 'marketing'],
            ['label' => 'Featured & bestsellers', 'icon' => 'ti-star', 'route' => 'admin.highlights.index', 'active' => 'admin.highlights.*', 'section' => 'marketing'],
            ['label' => 'Pricing plans', 'icon' => 'ti-crown', 'route' => 'admin.pricing-plans.index', 'active' => 'admin.pricing-plans.*', 'section' => 'marketing'],
            ['label' => 'Subscribers', 'icon' => 'ti-mail', 'route' => 'admin.subscribers.index', 'active' => 'admin.subscribers.*', 'section' => 'marketing'],
        ],
        'Content' => [
            ['label' => 'Pages', 'icon' => 'ti-file-text', 'route' => 'admin.pages.index', 'active' => ['admin.pages.*', 'admin.page-content.*'], 'section' => 'content'],
            ['label' => 'Blog posts', 'icon' => 'ti-article', 'route' => 'admin.posts.index', 'active' => 'admin.posts.*', 'section' => 'content'],
            ['label' => 'Testimonials', 'icon' => 'ti-message-heart', 'route' => 'admin.testimonials.index', 'active' => 'admin.testimonials.*', 'section' => 'content'],
            ['label' => 'Contact messages', 'icon' => 'ti-inbox', 'route' => 'admin.messages.index', 'active' => 'admin.messages.*', 'section' => 'messages', 'badge' => $unreadMessages],
        ],
        'Settings' => [
            ['label' => 'Store settings', 'icon' => 'ti-settings', 'route' => 'admin.settings.edit', 'active' => 'admin.settings.*', 'section' => 'settings'],
            ['label' => 'Admin users', 'icon' => 'ti-users', 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'section' => 'users'],
        ],
    ];
    // Only show what this user's role can open.
    $menu = collect($menu)->map(fn ($items) => array_values(array_filter($items, fn ($item) => $user->canAccessSection($item['section']))))->filter()->all();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · {{ $storeName }} Admin</title>
    @fonts('public-sans')
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="min-h-screen bg-page font-sans text-ink antialiased" x-data="adminShell">

    {{-- Mobile overlay --}}
    <div x-cloak x-show="mobileOpen" x-transition.opacity @click="mobileOpen = false"
         class="fixed inset-0 z-30 bg-navy/40 lg:hidden"></div>

    {{-- Sidebar --}}
    <aside @mouseenter="hovering = true" @mouseleave="hovering = false"
           :class="[
               mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
               compact ? 'lg:w-[78px]' : 'lg:w-[260px]',
           ]"
           class="fixed inset-y-0 left-0 z-40 flex w-[260px] flex-col bg-white shadow-nav transition-all duration-300">

        <div class="flex h-20 shrink-0 items-center justify-between px-5">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 overflow-hidden" aria-label="{{ $storeName }} admin home">
                {{-- Your logo when the menu is open; a small book icon when it's collapsed to icons. --}}
                <span @if ($logo) x-show="compact" x-cloak @endif class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary text-white">
                    <i class="ti ti-book text-xl"></i>
                </span>
                @if ($logo)
                    <img x-show="!compact" src="{{ $logo }}" alt="{{ $storeName }}" class="h-12 w-auto max-w-44 object-contain">
                @else
                    <span x-show="!compact" class="text-xl font-bold whitespace-nowrap text-navy">{{ $storeName }}</span>
                @endif
            </a>
            <button type="button" @click="toggleCollapsed()" x-show="!compact"
                    class="hidden size-7 items-center justify-center rounded-full text-muted hover:bg-page hover:text-ink lg:flex"
                    :title="collapsed ? 'Keep menu open' : 'Collapse menu'">
                <i class="ti text-lg" :class="collapsed ? 'ti-circle' : 'ti-circle-dot'"></i>
            </button>
            <button type="button" @click="mobileOpen = false" class="text-muted lg:hidden" aria-label="Close menu">
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto px-3.5 pb-6">
            @foreach ($menu as $section => $items)
                @if ($section)
                    <div class="mt-5 mb-1.5 flex h-5 items-center px-3 text-[11px] font-medium tracking-wider text-muted/80 uppercase">
                        <span x-show="!compact" class="whitespace-nowrap">{{ $section }}</span>
                        <span x-show="compact" x-cloak class="mx-auto h-px w-5 bg-line"></span>
                    </div>
                @endif
                @foreach ($items as $item)
                    @php $active = request()->routeIs($item['active']); @endphp
                    <a href="{{ route($item['route']) }}"
                       @class([
                           'mt-1 flex items-center gap-3 rounded-md px-3 py-2.5 text-[15px] transition',
                           'bg-primary text-white shadow-md shadow-primary/35' => $active,
                           'text-ink hover:bg-page' => ! $active,
                       ])
                       :class="compact && 'justify-center'"
                       aria-label="{{ $item['label'] }}" :title="compact ? @js($item['label']) : null"
                       @if ($active) aria-current="page" @endif>
                        <i class="ti {{ $item['icon'] }} text-[22px] leading-none"></i>
                        <span x-show="!compact" class="flex-1 whitespace-nowrap">{{ $item['label'] }}</span>
                        @if (! empty($item['badge']))
                            <span x-show="!compact" @class([
                                'rounded-full px-2 py-0.5 text-xs font-semibold',
                                'bg-white text-primary' => $active,
                                'bg-cyan text-white' => ! $active,
                            ])>{{ $item['badge'] }}</span>
                        @endif
                    </a>
                @endforeach
            @endforeach
        </nav>
    </aside>

    {{-- Page --}}
    <div class="flex min-h-screen flex-col transition-all duration-300" :class="collapsed ? 'lg:pl-[78px]' : 'lg:pl-[260px]'">

        {{-- Floating top bar --}}
        <header class="sticky top-0 z-20 px-4 pt-3 sm:px-6">
            <div class="flex h-14 items-center gap-3 rounded-lg bg-white/95 px-4 shadow-nav backdrop-blur">
                <button type="button" @click="mobileOpen = true" class="text-ink lg:hidden" aria-label="Open menu">
                    <i class="ti ti-menu-2 text-2xl"></i>
                </button>

                <form action="{{ route('admin.books.index') }}" method="GET" class="flex flex-1 items-center gap-2">
                    <i class="ti ti-search text-xl text-muted"></i>
                    <input type="search" name="search" value="{{ request()->routeIs('admin.books.index') ? request('search') : '' }}"
                           placeholder="Search books by title or ISBN"
                           class="w-full max-w-md border-0 bg-transparent p-0 text-sm text-ink placeholder:text-muted focus:ring-0 focus:outline-none">
                </form>

                <a href="{{ url('/') }}" target="_blank" title="View store"
                   class="flex size-9 items-center justify-center rounded-full text-ink hover:bg-page">
                    <i class="ti ti-building-store text-[22px]"></i>
                </a>

                <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape="open = false">
                    <button type="button" @click="open = !open" class="relative flex size-9 items-center justify-center rounded-full bg-primary-soft font-semibold text-primary" aria-label="Account menu">
                        {{ str($user->name)->substr(0, 1)->upper() }}
                        <span class="absolute right-0 bottom-0 size-2.5 rounded-full border-2 border-white bg-leaf"></span>
                    </button>
                    <div x-cloak x-show="open" x-transition.origin.top.right
                         class="absolute right-0 mt-3 w-60 rounded-lg bg-white py-2 shadow-card">
                        <div class="flex items-center gap-3 px-4 py-2">
                            <span class="flex size-9 items-center justify-center rounded-full bg-primary-soft font-semibold text-primary">{{ str($user->name)->substr(0, 1)->upper() }}</span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-ink">{{ $user->name }}</p>
                                <p class="truncate text-xs text-muted">{{ $user->role?->label() ?? 'Admin' }}</p>
                            </div>
                        </div>
                        <div class="my-2 h-px bg-line"></div>
                        <p class="truncate px-4 pb-2 text-xs text-muted">{{ $user->email }}</p>
                        <form method="POST" action="{{ route('admin.logout') }}" class="px-3">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 rounded-md px-2 py-2 text-sm text-ink hover:bg-page">
                                <i class="ti ti-logout text-lg"></i> Log out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-[1440px] flex-1 px-4 py-6 sm:px-6">
            <x-admin.flash />
            {{ $slot }}
        </main>

        <footer class="mx-auto flex w-full max-w-[1440px] flex-wrap items-center justify-between gap-2 px-4 pb-5 text-sm text-muted sm:px-6">
            <span>© {{ date('Y') }} {{ $storeName }}</span>
            <span>Admin panel</span>
        </footer>
    </div>
</body>
</html>
