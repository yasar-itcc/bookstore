@use('Illuminate\Support\Number')
@use('App\Enums\BookFormat', 'Format')

<x-admin.layout title="Dashboard">
    <div class="grid grid-cols-12 gap-6">

        {{-- Welcome --}}
        <div class="admin-card col-span-12 overflow-hidden p-6 md:col-span-5 xl:col-span-4">
            <div class="flex h-full flex-col justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-navy">Welcome back, {{ str(auth()->user()->name)->before(' ') }}</h2>
                    <p class="text-sm text-muted">Here's your catalog at a glance.</p>
                </div>
                <div>
                    <p class="text-3xl font-bold text-primary">{{ Number::format($stats['activeBooks']) }}</p>
                    <p class="text-sm text-muted">of {{ Number::format($stats['books']) }} books are live</p>
                </div>
                <div>
                    <a href="{{ route('admin.books.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add book</a>
                </div>
            </div>
        </div>

        {{-- Statistics --}}
        <div class="admin-card col-span-12 p-6 md:col-span-7 xl:col-span-8">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-navy">Catalog statistics</h2>
                <span class="text-xs text-muted">Updated just now</span>
            </div>
            <div class="grid grid-cols-2 gap-6 xl:grid-cols-4">
                @foreach ([
                    ['Books', $stats['books'], 'ti-books', 'bg-primary-soft text-primary', route('admin.books.index')],
                    ['Categories', $stats['categories'], 'ti-category', 'bg-cyan-soft text-cyan', route('admin.categories.index')],
                    ['Authors', $stats['authors'], 'ti-feather', 'bg-leaf-soft text-leaf-dark', route('admin.authors.index')],
                    ['Publishers', $stats['publishers'], 'ti-building-store', 'bg-amber-50 text-amber-600', route('admin.publishers.index')],
                ] as [$label, $value, $icon, $colors, $url])
                    <a href="{{ $url }}" class="group flex items-center gap-3">
                        <span class="flex size-11 items-center justify-center rounded-md {{ $colors }}"><i class="ti {{ $icon }} text-2xl"></i></span>
                        <span>
                            <span class="block text-lg font-semibold text-navy">{{ Number::format($value) }}</span>
                            <span class="block text-sm text-muted group-hover:text-primary">{{ $label }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Small stat cards --}}
        @foreach ([
            ['Stock value', Number::currency($stats['stockValue'], in: 'INR', locale: 'en_IN', precision: 0), 'At selling price', 'ti-currency-rupee', 'bg-primary-soft text-primary'],
            ['Units in stock', Number::format($stats['unitsInStock']), 'Paperback and hardcover', 'ti-packages', 'bg-cyan-soft text-cyan'],
            ['Low stock', Number::format($stats['lowStock']), $stats['outOfStock'].' out of stock', 'ti-alert-triangle', 'bg-amber-50 text-amber-600'],
            ['eBooks', Number::format($stats['ebooks']), 'Digital editions', 'ti-device-tablet', 'bg-leaf-soft text-leaf-dark'],
        ] as [$label, $value, $note, $icon, $colors])
            <div class="admin-card col-span-12 p-5 sm:col-span-6 xl:col-span-3">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-muted">{{ $label }}</p>
                        <p class="mt-1 text-2xl font-semibold text-navy">{{ $value }}</p>
                    </div>
                    <span class="flex size-10 items-center justify-center rounded-md {{ $colors }}"><i class="ti {{ $icon }} text-2xl"></i></span>
                </div>
                <p class="mt-3 text-xs text-muted">{{ $note }}</p>
            </div>
        @endforeach

        {{-- Books by category --}}
        <div class="admin-card col-span-12 p-6 lg:col-span-6 xl:col-span-5">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-navy">Books by category</h2>
                <a href="{{ route('admin.categories.index') }}" class="text-sm text-primary hover:underline">View all</a>
            </div>
            @php $maxBooks = max(1, $topCategories->max('books_count')); @endphp
            @forelse ($topCategories as $category)
                <div class="mb-4 last:mb-0">
                    <div class="mb-1.5 flex justify-between text-sm">
                        <span class="text-ink">{{ $category->name }}</span>
                        <span class="font-medium text-navy">{{ $category->books_count }}</span>
                    </div>
                    <div class="h-2 rounded-full bg-page">
                        <div class="h-2 rounded-full bg-primary" style="width: {{ round($category->books_count / $maxBooks * 100) }}%"></div>
                    </div>
                </div>
            @empty
                <x-admin.empty-state icon="ti-category" title="No categories yet" text="Create categories to organise your books." />
            @endforelse
        </div>

        {{-- Formats --}}
        <div class="admin-card col-span-12 p-6 sm:col-span-6 lg:col-span-6 xl:col-span-3">
            <h2 class="mb-5 text-lg font-semibold text-navy">Formats</h2>
            @php $totalFormats = max(1, $formatCounts->sum()); @endphp
            @foreach (Format::cases() as $format)
                @php $count = (int) ($formatCounts[$format->value] ?? 0); @endphp
                <div class="mb-5 flex items-center gap-3 last:mb-0">
                    <span class="flex size-9 items-center justify-center rounded-md bg-page text-primary"><i class="ti {{ $format->icon() }} text-xl"></i></span>
                    <div class="flex-1">
                        <div class="flex justify-between text-sm"><span>{{ $format->label() }}</span><span class="font-medium text-navy">{{ $count }}</span></div>
                        <div class="mt-1.5 h-1.5 rounded-full bg-page"><div class="h-1.5 rounded-full bg-cyan" style="width: {{ round($count / $totalFormats * 100) }}%"></div></div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Orders (coming later) --}}
        <div class="admin-card col-span-12 flex flex-col items-center justify-center p-6 text-center sm:col-span-6 lg:col-span-12 xl:col-span-4">
            <span class="mb-4 flex size-14 items-center justify-center rounded-full bg-leaf-soft text-leaf-dark"><i class="ti ti-shopping-cart text-3xl"></i></span>
            <h2 class="text-lg font-semibold text-navy">Orders and sales</h2>
            <p class="mt-1 max-w-xs text-sm text-muted">Cash on Delivery orders, revenue and customers will show here once checkout is built (phase 3).</p>
        </div>

        {{-- Recently added --}}
        <div class="admin-card col-span-12 overflow-hidden xl:col-span-8">
            <div class="flex items-center justify-between p-6 pb-4">
                <h2 class="text-lg font-semibold text-navy">Recently added books</h2>
                <a href="{{ route('admin.books.index') }}" class="text-sm text-primary hover:underline">View all</a>
            </div>
            @if ($recentBooks->isEmpty())
                <x-admin.empty-state title="No books yet" text="Add your first book to start building the catalog.">
                    <a href="{{ route('admin.books.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add book</a>
                </x-admin.empty-state>
            @else
                <div class="overflow-x-auto">
                    <table class="admin-table">
                        <thead><tr><th>Book</th><th>Category</th><th>Price from</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach ($recentBooks as $book)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.books.edit', $book) }}" class="flex items-center gap-3">
                                        @include('admin.books.partials.cover', ['book' => $book, 'size' => 'h-12 w-9'])
                                        <span class="min-w-0">
                                            <span class="block truncate font-medium text-navy hover:text-primary">{{ $book->title }}</span>
                                            <span class="block truncate text-xs text-muted">{{ $book->authors->pluck('name')->join(', ') ?: 'No author' }}</span>
                                        </span>
                                    </a>
                                </td>
                                <td class="text-muted">{{ $book->category?->name }}</td>
                                <td>{{ $book->startingPrice() !== null ? Number::currency($book->startingPrice(), in: 'INR', locale: 'en_IN') : '—' }}</td>
                                <td><x-admin.status-badge :active="$book->is_active" on="Live" off="Hidden" /></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Low stock --}}
        <div class="admin-card col-span-12 p-6 xl:col-span-4">
            <h2 class="mb-1 text-lg font-semibold text-navy">Low stock</h2>
            <p class="mb-5 text-sm text-muted">{{ \App\Models\BookFormat::LOW_STOCK_THRESHOLD }} or fewer copies left</p>
            @forelse ($lowStockItems as $item)
                <a href="{{ route('admin.books.edit', $item->book) }}" class="mb-4 flex items-center justify-between gap-3 last:mb-0">
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-medium text-navy hover:text-primary">{{ $item->book->title }}</span>
                        <span class="block text-xs text-muted">{{ $item->format->label() }}</span>
                    </span>
                    <span @class([
                        'shrink-0 rounded px-2 py-0.5 text-xs font-medium',
                        'bg-amber-50 text-amber-700' => $item->stock > 0,
                        'bg-slate-100 text-slate-500' => ! $item->stock,
                    ])>{{ $item->stock ? $item->stock.' left' : 'Out of stock' }}</span>
                </a>
            @empty
                <div class="flex items-center gap-3 rounded-md bg-leaf-soft px-4 py-3 text-sm text-leaf-dark">
                    <i class="ti ti-circle-check text-xl"></i> All physical books are well stocked.
                </div>
            @endforelse
        </div>
    </div>
</x-admin.layout>
