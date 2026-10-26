<x-admin.layout title="Pages">
    <x-admin.page-header title="Pages" subtitle="Your store's main pages, plus policies, FAQ and any other pages you write."
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Content' => null]">
        <x-slot:actions>
            <a href="{{ route('admin.pages.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add page</a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Built-in pages: their sections and text are edited in a form, not written as an article. --}}
    <h2 class="mb-3 text-sm font-semibold tracking-wide text-muted uppercase">Main pages</h2>
    <div class="mb-8 grid gap-6 md:grid-cols-3">
        @foreach (\App\Support\SettingsSchema::groups('pages') as $key => $info)
            <div class="admin-card flex flex-col p-6">
                <div class="mb-4 flex items-center gap-3">
                    <span class="flex size-11 items-center justify-center rounded-md bg-primary-soft text-primary"><i class="ti {{ $info['icon'] }} text-2xl"></i></span>
                    <div>
                        <a href="{{ route('admin.page-content.edit', $key) }}" class="font-semibold text-navy hover:text-primary">{{ $info['label'] }}</a>
                        <p class="text-xs text-muted">{{ $info['url'] }}</p>
                    </div>
                </div>
                <p class="mb-5 flex-1 text-sm text-muted">{{ $info['description'] }}</p>
                <div class="flex gap-2">
                    <a href="{{ route('admin.page-content.edit', $key) }}" class="btn btn-primary flex-1"><i class="ti ti-pencil"></i> Edit page</a>
                    <a href="{{ url($info['url']) }}" target="_blank" class="btn btn-secondary" title="View page"><i class="ti ti-external-link"></i></a>
                </div>
            </div>
        @endforeach
    </div>

    <h2 class="mb-3 text-sm font-semibold tracking-wide text-muted uppercase">Other pages</h2>
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
