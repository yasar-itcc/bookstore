<x-admin.layout title="Publishers">
    <x-admin.page-header title="Publishers" subtitle="Companies that publish your books."
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Catalog' => null]">
        <x-slot:actions>
            <a href="{{ route('admin.publishers.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add publisher</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="admin-card overflow-hidden">
        @include('admin.partials.search', ['placeholder' => 'Search publishers'])

        @if ($publishers->isEmpty())
            <x-admin.empty-state icon="ti-building-store" :title="request('search') ? 'No publishers match your search' : 'No publishers yet'">
                <a href="{{ route('admin.publishers.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add publisher</a>
            </x-admin.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead><tr><th>Publisher</th><th>Website</th><th>Books</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                    @foreach ($publishers as $publisher)
                        <tr>
                            <td>
                                <a href="{{ route('admin.publishers.edit', $publisher) }}" class="font-medium text-navy hover:text-primary">{{ $publisher->name }}</a>
                                <p class="text-xs text-muted">/{{ $publisher->slug }}</p>
                            </td>
                            <td class="text-muted">
                                @if ($publisher->website)
                                    <a href="{{ $publisher->website }}" target="_blank" rel="noopener" class="hover:text-primary">{{ str($publisher->website)->after('://')->rtrim('/') }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $publisher->books_count }}</td>
                            <td><x-admin.status-badge :active="$publisher->is_active" /></td>
                            <td>
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('admin.publishers.edit', $publisher) }}" class="flex size-8 items-center justify-center rounded-full text-muted hover:bg-page hover:text-primary" title="Edit"><i class="ti ti-pencil text-lg"></i></a>
                                    <x-admin.delete-button :action="route('admin.publishers.destroy', $publisher)" :name="$publisher->name" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @include('admin.partials.pagination', ['paginator' => $publishers])
        @endif
    </div>
</x-admin.layout>
