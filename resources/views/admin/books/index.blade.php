@use('Illuminate\Support\Number')
@use('App\Enums\BookFormat', 'Format')

<x-admin.layout title="Books">
    <x-admin.page-header title="Books" subtitle="Every book in your catalog, with its formats and prices."
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Catalog' => null]">
        <x-slot:actions>
            <a href="{{ route('admin.books.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add book</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="admin-card overflow-hidden">
        <form method="GET" class="grid gap-3 border-b border-line p-5 sm:grid-cols-2 lg:grid-cols-[1fr_200px_160px_160px_auto]">
            <div class="relative">
                <i class="ti ti-search pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-muted"></i>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search title or ISBN" class="admin-input pl-9">
            </div>
            <select name="category" class="admin-input" aria-label="Category">
                <option value="">All categories</option>
                @foreach ($categories as $id => $name)
                    <option value="{{ $id }}" @selected((string) request('category') === (string) $id)>{{ $name }}</option>
                @endforeach
            </select>
            <select name="format" class="admin-input" aria-label="Format">
                <option value="">All formats</option>
                @foreach (Format::cases() as $format)
                    <option value="{{ $format->value }}" @selected(request('format') === $format->value)>{{ $format->label() }}</option>
                @endforeach
            </select>
            <select name="status" class="admin-input" aria-label="Status">
                <option value="">Any status</option>
                <option value="live" @selected(request('status') === 'live')>Live</option>
                <option value="hidden" @selected(request('status') === 'hidden')>Hidden</option>
            </select>
            <div class="flex items-center gap-3">
                <button type="submit" class="btn btn-secondary">Filter</button>
                @if (request()->hasAny(['search', 'category', 'status', 'format']))
                    <a href="{{ route('admin.books.index') }}" class="text-sm text-muted hover:text-primary">Clear</a>
                @endif
            </div>
        </form>

        @if ($books->isEmpty())
            @if (request()->hasAny(['search', 'category', 'status', 'format']))
                <x-admin.empty-state icon="ti-search" title="No books match these filters" text="Try a different search or clear the filters." />
            @else
                <x-admin.empty-state title="No books yet" text="Add your first book: its details, cover, and the formats you sell it in.">
                    <a href="{{ route('admin.books.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add book</a>
                </x-admin.empty-state>
            @endif
        @else
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead><tr><th>Book</th><th>Category</th><th>Formats and prices</th><th>Stock</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                    @foreach ($books as $book)
                        @php
                            $physical = $book->formats->filter(fn ($f) => ! $f->format->isDigital());
                            $stock = $physical->sum('stock');
                        @endphp
                        <tr>
                            <td class="min-w-64">
                                <a href="{{ route('admin.books.edit', $book) }}" class="flex items-center gap-3">
                                    @include('admin.books.partials.cover', ['book' => $book, 'size' => 'h-14 w-10'])
                                    <span class="min-w-0">
                                        <span class="flex items-center gap-1.5 font-medium text-navy hover:text-primary">
                                            {{ $book->title }}
                                            @if ($book->is_featured)<i class="ti ti-star text-amber-500" title="Featured"></i>@endif
                                        </span>
                                        <span class="block text-xs text-muted">{{ $book->authors->pluck('name')->join(', ') ?: 'No author' }}</span>
                                        @if ($book->isbn)<span class="block text-xs text-muted">ISBN {{ $book->isbn }}</span>@endif
                                    </span>
                                </a>
                            </td>
                            <td class="text-muted">{{ $book->category?->name }}</td>
                            <td>
                                <div class="flex flex-col gap-1">
                                    @forelse ($book->formats->sortBy(fn ($f) => $f->format->value) as $format)
                                        <span class="flex items-center gap-1.5 text-xs">
                                            <i class="ti {{ $format->format->icon() }} text-muted"></i>
                                            <span class="w-16 text-muted">{{ $format->format->label() }}</span>
                                            <span class="font-medium text-navy">{{ Number::currency($format->price, in: 'INR', locale: 'en_IN') }}</span>
                                            @if ($format->discountPercent())
                                                <span class="rounded bg-amber-50 px-1 text-amber-700">{{ $format->discountPercent() }}% off</span>
                                            @endif
                                        </span>
                                    @empty
                                        <span class="text-xs text-amber-700">No formats</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>
                                @if ($physical->isEmpty())
                                    <span class="text-xs text-muted">Digital only</span>
                                @elseif ($stock === 0)
                                    <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">Out of stock</span>
                                @elseif ($physical->contains(fn ($f) => $f->stock > 0 && $f->stock <= \App\Models\BookFormat::LOW_STOCK_THRESHOLD))
                                    <span class="rounded bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700">{{ $stock }} · low</span>
                                @else
                                    <span class="text-sm">{{ $stock }}</span>
                                @endif
                            </td>
                            <td><x-admin.status-badge :active="$book->is_active" on="Live" off="Hidden" /></td>
                            <td>
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('admin.books.edit', $book) }}" class="flex size-8 items-center justify-center rounded-full text-muted hover:bg-page hover:text-primary" title="Edit"><i class="ti ti-pencil text-lg"></i></a>
                                    <x-admin.delete-button :action="route('admin.books.destroy', $book)" :name="$book->title" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @include('admin.partials.pagination', ['paginator' => $books])
        @endif
    </div>
</x-admin.layout>
