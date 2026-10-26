<x-admin.layout title="Authors">
    <x-admin.page-header title="Authors" subtitle="Writers of the books in your store."
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Catalog' => null]">
        <x-slot:actions>
            <a href="{{ route('admin.authors.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add author</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="admin-card overflow-hidden">
        @include('admin.partials.search', ['placeholder' => 'Search authors'])

        @if ($authors->isEmpty())
            <x-admin.empty-state icon="ti-feather" :title="request('search') ? 'No authors match your search' : 'No authors yet'">
                <a href="{{ route('admin.authors.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add author</a>
            </x-admin.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead><tr><th>Author</th><th>Books</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                    @foreach ($authors as $author)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if ($author->photo)
                                        <img src="{{ Storage::disk('public')->url($author->photo) }}" alt="" class="size-9 rounded-full object-cover">
                                    @else
                                        <span class="flex size-9 items-center justify-center rounded-full bg-leaf-soft font-semibold text-leaf-dark">{{ str($author->name)->substr(0, 1)->upper() }}</span>
                                    @endif
                                    <div>
                                        <a href="{{ route('admin.authors.edit', $author) }}" class="font-medium text-navy hover:text-primary">{{ $author->name }}</a>
                                        <p class="max-w-md truncate text-xs text-muted">{{ str($author->bio)->limit(80) ?: '/'.$author->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $author->books_count }}</td>
                            <td><x-admin.status-badge :active="$author->is_active" /></td>
                            <td>
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('admin.authors.edit', $author) }}" class="flex size-8 items-center justify-center rounded-full text-muted hover:bg-page hover:text-primary" title="Edit"><i class="ti ti-pencil text-lg"></i></a>
                                    <x-admin.delete-button :action="route('admin.authors.destroy', $author)" :name="$author->name" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @include('admin.partials.pagination', ['paginator' => $authors])
        @endif
    </div>
</x-admin.layout>
