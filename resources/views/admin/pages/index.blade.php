<x-admin.layout title="Pages">
    <x-admin.page-header title="Pages" subtitle="Policies, FAQ and other simple pages. Home, About and Pricing are set up under Settings."
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Content' => null]">
        <x-slot:actions>
            <a href="{{ route('admin.pages.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add page</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="admin-card overflow-hidden">
        @if ($pages->isEmpty())
            <x-admin.empty-state icon="ti-file-text" title="No pages yet" text="Add your Privacy Policy, Terms, Shipping and Returns policies and FAQ.">
                <a href="{{ route('admin.pages.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add page</a>
            </x-admin.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead><tr><th>Page</th><th>Web address</th><th>In footer</th><th>Status</th><th>Updated</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                    @foreach ($pages as $page)
                        <tr>
                            <td><a href="{{ route('admin.pages.edit', $page) }}" class="font-medium text-navy hover:text-primary">{{ $page->title }}</a></td>
                            <td><a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="text-sm text-muted hover:text-primary">/{{ $page->slug }} <i class="ti ti-external-link"></i></a></td>
                            <td>{!! $page->show_in_footer ? '<i class="ti ti-check text-leaf"></i>' : '<span class="text-muted">—</span>' !!}</td>
                            <td><x-admin.status-badge :active="$page->is_published" on="Published" off="Draft" /></td>
                            <td class="text-muted">{{ $page->updated_at->diffForHumans() }}</td>
                            <td><x-admin.row-actions :edit="route('admin.pages.edit', $page)" :destroy="route('admin.pages.destroy', $page)" :name="$page->title" /></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-admin.layout>
