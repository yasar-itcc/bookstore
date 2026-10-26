@php $editing = $page->exists; @endphp

<x-admin.layout :title="$editing ? 'Edit page' : 'Add page'">
    <x-admin.page-header :title="$editing ? 'Edit page' : 'Add page'"
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Pages' => route('admin.pages.index'), ($editing ? $page->title : 'Add') => null]">
        @if ($editing && $page->is_published)
            <x-slot:actions>
                <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="btn btn-secondary"><i class="ti ti-external-link"></i> View page</a>
            </x-slot:actions>
        @endif
    </x-admin.page-header>

    <form method="POST" action="{{ $editing ? route('admin.pages.update', $page) : route('admin.pages.store') }}" class="grid grid-cols-12 gap-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="admin-card col-span-12 space-y-5 p-6 lg:col-span-8">
            <x-admin.input name="title" label="Title" :value="$page->title" required maxlength="150" placeholder="Shipping Policy" />
            <x-admin.markdown name="content" :value="$page->content" rows="20" />
        </div>

        <div class="col-span-12 space-y-6 lg:col-span-4">
            <div class="admin-card space-y-5 p-6">
                <x-admin.input name="slug" label="Web address" :value="$page->slug" placeholder="shipping-policy" hint="yourstore.com/… Leave empty to create it from the title." />
                <x-admin.textarea name="meta_description" label="Search description" :value="$page->meta_description" rows="3" hint="Shown under the page in Google. About 155 characters." />
                <x-admin.toggle name="show_in_footer" label="Link in footer" :checked="$page->show_in_footer" />
                <x-admin.toggle name="is_published" label="Published" hint="Drafts aren't visible on the store." :checked="$page->is_published" />
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1">{{ $editing ? 'Save changes' : 'Create page' }}</button>
                <a href="{{ route('admin.pages.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</x-admin.layout>
