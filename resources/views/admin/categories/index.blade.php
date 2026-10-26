<x-admin.layout title="Categories">
    <x-admin.page-header title="Categories" subtitle="Group your books so customers can browse them."
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Catalog' => null]">
        <x-slot:actions>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add category</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="admin-card overflow-hidden">
        @include('admin.partials.search', ['placeholder' => 'Search categories'])

        @if ($categories->isEmpty())
            <x-admin.empty-state icon="ti-category" :title="request('search') ? 'No categories match your search' : 'No categories yet'"
                                 text="Examples: Fiction, Islamic, Children, Education.">
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add category</a>
            </x-admin.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead><tr><th>Category</th><th>Parent</th><th>Books</th><th>Order</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                    @foreach ($categories as $category)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if ($category->image)
                                        <img src="{{ Storage::disk('public')->url($category->image) }}" alt="" class="size-9 rounded object-cover">
                                    @else
                                        <span class="flex size-9 items-center justify-center rounded bg-cyan-soft text-cyan"><i class="ti ti-category text-lg"></i></span>
                                    @endif
                                    <div>
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="font-medium text-navy hover:text-primary">{{ $category->name }}</a>
                                        <p class="text-xs text-muted">/{{ $category->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted">{{ $category->parent?->name ?? '—' }}</td>
                            <td>
                                <a href="{{ route('admin.books.index', ['category' => $category->id]) }}" class="hover:text-primary">{{ $category->books_count }}</a>
                            </td>
                            <td class="text-muted">{{ $category->sort_order }}</td>
                            <td><x-admin.status-badge :active="$category->is_active" /></td>
                            <td>
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="flex size-8 items-center justify-center rounded-full text-muted hover:bg-page hover:text-primary" title="Edit"><i class="ti ti-pencil text-lg"></i></a>
                                    <x-admin.delete-button :action="route('admin.categories.destroy', $category)" :name="$category->name" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @include('admin.partials.pagination', ['paginator' => $categories])
        @endif
    </div>
</x-admin.layout>
