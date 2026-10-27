@php $editing = $author->exists; @endphp

<x-admin.layout :title="$editing ? 'Edit author' : 'Add author'">
    <x-admin.page-header :title="$editing ? 'Edit author' : 'Add author'"
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Authors' => route('admin.authors.index'), ($editing ? $author->name : 'Add') => null]" />

    <form method="POST" enctype="multipart/form-data"
          action="{{ $editing ? route('admin.authors.update', $author) : route('admin.authors.store') }}"
          class="grid grid-cols-12 gap-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="admin-card col-span-12 space-y-5 p-6 lg:col-span-8">
            <h2 class="text-lg font-semibold text-navy">Author details</h2>
            <x-admin.input name="name" label="Name" :value="$author->name" required maxlength="120" placeholder="A. P. J. Abdul Kalam" />
            <x-admin.input name="slug" label="URL slug" :value="$author->slug" hint="Leave empty to create it from the name." />
            <x-admin.textarea name="bio" label="Biography" :value="$author->bio" rows="6" />
        </div>

        <div class="col-span-12 space-y-6 lg:col-span-4">
            <div class="admin-card p-6">
                <x-admin.toggle name="is_active" label="Active" hint="Show this author in the store." :checked="$author->is_active" />
            </div>
            <div class="admin-card p-6">
                @include('admin.partials.image-upload', [
                    'name' => 'photo', 'label' => 'Photo', 'removeName' => 'remove_photo',
                    'current' => $author->photo ? Storage::disk('public')->url($author->photo) : null,
                ])
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1">{{ $editing ? 'Save changes' : 'Create author' }}</button>
                <a href="{{ route('admin.authors.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>

    {{-- Books by this author --}}
    @if ($editing)
        <div class="admin-card mt-6 overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 p-6 pb-4">
                <div>
                    <h2 class="text-lg font-semibold text-navy">Books by {{ $author->name }}</h2>
                    <p class="text-sm text-muted">{{ $books->count() }} {{ Str::plural('book', $books->count()) }}. To add or remove a book, edit the book and change its authors.</p>
                </div>
                <div class="flex gap-2">
                    @if ($author->is_active && $books->contains('is_active', true))
                        <a href="{{ route('authors.show', $author) }}" target="_blank" class="btn btn-secondary"><i class="ti ti-external-link"></i> View on store</a>
                    @endif
                    <a href="{{ route('admin.books.create') }}" class="btn btn-secondary"><i class="ti ti-plus"></i> Add book</a>
                </div>
            </div>

            @if ($books->isEmpty())
                <x-admin.empty-state icon="ti-books" title="No books yet" text="Books appear here once you pick this author on a book's edit page." />
            @else
                <div class="overflow-x-auto">
                    <table class="admin-table">
                        <thead><tr><th>Book</th><th>Category</th><th>Formats and prices</th><th>Stock</th><th>Status</th><th class="text-right">Edit</th></tr></thead>
                        <tbody>
                        @foreach ($books as $book)
                            @php $physical = $book->formats->filter(fn ($f) => ! $f->format->isDigital()); @endphp
                            <tr>
                                <td class="min-w-64">
                                    <a href="{{ route('admin.books.edit', $book) }}" class="flex items-center gap-3">
                                        @include('admin.books.partials.cover', ['book' => $book, 'size' => 'h-12 w-9'])
                                        <span>
                                            <span class="block font-medium text-navy hover:text-primary">{{ $book->title }}</span>
                                            @if ($book->published_year)<span class="text-xs text-muted">{{ $book->published_year }}</span>@endif
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
                                                <span class="font-medium text-navy">{{ money($format->price) }}</span>
                                            </span>
                                        @empty
                                            <span class="text-xs text-amber-700">No formats</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td>{{ $physical->isEmpty() ? 'Digital only' : $physical->sum('stock') }}</td>
                                <td><x-admin.status-badge :active="$book->is_active" on="Live" off="Hidden" /></td>
                                <td>
                                    <div class="flex justify-end">
                                        <a href="{{ route('admin.books.edit', $book) }}" class="flex size-8 items-center justify-center rounded-full text-muted hover:bg-page hover:text-primary" title="Edit book"><i class="ti ti-pencil text-lg"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
</x-admin.layout>
