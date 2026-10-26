<x-admin.layout title="Languages">
    <x-admin.page-header title="Languages" subtitle="Languages your books are written in."
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Catalog' => null]">
        <x-slot:actions>
            <a href="{{ route('admin.languages.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add language</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="admin-card overflow-hidden">
        @include('admin.partials.search', ['placeholder' => 'Search languages'])

        @if ($languages->isEmpty())
            <x-admin.empty-state icon="ti-language" :title="request('search') ? 'No languages match your search' : 'No languages yet'">
                <a href="{{ route('admin.languages.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add language</a>
            </x-admin.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead><tr><th>Language</th><th>Code</th><th>Books</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                    @foreach ($languages as $language)
                        <tr>
                            <td><a href="{{ route('admin.languages.edit', $language) }}" class="font-medium text-navy hover:text-primary">{{ $language->name }}</a></td>
                            <td><span class="rounded bg-page px-2 py-0.5 font-mono text-xs uppercase">{{ $language->code }}</span></td>
                            <td>{{ $language->books_count }}</td>
                            <td><x-admin.status-badge :active="$language->is_active" /></td>
                            <td>
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('admin.languages.edit', $language) }}" class="flex size-8 items-center justify-center rounded-full text-muted hover:bg-page hover:text-primary" title="Edit"><i class="ti ti-pencil text-lg"></i></a>
                                    <x-admin.delete-button :action="route('admin.languages.destroy', $language)" :name="$language->name" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @include('admin.partials.pagination', ['paginator' => $languages])
        @endif
    </div>
</x-admin.layout>
