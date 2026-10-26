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
</x-admin.layout>
