<x-admin.layout title="Featured & bestsellers">
    <x-admin.page-header title="Featured & bestsellers"
                         subtitle="Featured books appear in the home page slider and spotlight. Bestsellers fill the “Bestsellers of the week” row."
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Marketing' => null]" />

    <div class="mb-6 grid gap-6 sm:grid-cols-2">
        <a href="{{ route('admin.highlights.index', ['show' => 'featured']) }}" class="admin-card flex items-center gap-4 p-5 hover:ring-2 hover:ring-primary/30">
            <span class="flex size-11 items-center justify-center rounded-md bg-amber-50 text-amber-600"><i class="ti ti-star text-2xl"></i></span>
            <span><span class="block text-2xl font-semibold text-navy">{{ $counts['featured'] }}</span><span class="text-sm text-muted">Featured books</span></span>
        </a>
        <a href="{{ route('admin.highlights.index', ['show' => 'bestseller']) }}" class="admin-card flex items-center gap-4 p-5 hover:ring-2 hover:ring-primary/30">
            <span class="flex size-11 items-center justify-center rounded-md bg-leaf-soft text-leaf-dark"><i class="ti ti-flame text-2xl"></i></span>
            <span><span class="block text-2xl font-semibold text-navy">{{ $counts['bestseller'] }}</span><span class="text-sm text-muted">Bestsellers</span></span>
        </a>
    </div>

    <div class="admin-card overflow-hidden">
        <form method="GET" class="flex flex-wrap items-center gap-3 border-b border-line p-5">
            <div class="relative min-w-56 flex-1 sm:max-w-xs">
                <i class="ti ti-search pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-muted"></i>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search books" class="admin-input pl-9">
            </div>
            <select name="show" class="admin-input w-auto" aria-label="Show">
                <option value="">All books</option>
                <option value="featured" @selected(request('show') === 'featured')>Featured only</option>
                <option value="bestseller" @selected(request('show') === 'bestseller')>Bestsellers only</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead><tr><th>Book</th><th>Category</th><th class="text-center">Featured</th><th class="text-center">Bestseller</th></tr></thead>
                <tbody>
                @forelse ($books as $book)
                    <tr>
                        <td>
                            <a href="{{ route('admin.books.edit', $book) }}" class="flex items-center gap-3">
                                @include('admin.books.partials.cover', ['book' => $book, 'size' => 'h-12 w-9'])
                                <span><span class="block font-medium text-navy hover:text-primary">{{ $book->title }}</span>
                                    <span class="block text-xs text-muted">{{ $book->authors->pluck('name')->join(', ') }}</span></span>
                            </a>
                        </td>
                        <td class="text-muted">{{ $book->category?->name }}</td>
                        @foreach (['is_featured' => 'ti-star', 'is_bestseller' => 'ti-flame'] as $field => $icon)
                            <td class="text-center">
                                <form method="POST" action="{{ route('admin.highlights.update', $book) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="field" value="{{ $field }}">
                                    <input type="hidden" name="value" value="{{ $book->$field ? 0 : 1 }}">
                                    <button type="submit" @class([
                                        'inline-flex size-9 items-center justify-center rounded-full transition',
                                        'bg-primary text-white shadow-sm shadow-primary/30' => $book->$field,
                                        'bg-page text-muted hover:bg-primary-soft hover:text-primary' => ! $book->$field,
                                    ]) title="{{ $book->$field ? 'Remove' : 'Add' }}" aria-pressed="{{ $book->$field ? 'true' : 'false' }}">
                                        <i class="ti {{ $icon }} text-lg"></i>
                                    </button>
                                </form>
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="4"><x-admin.empty-state icon="ti-star" title="No books match" /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['paginator' => $books])
    </div>
</x-admin.layout>
