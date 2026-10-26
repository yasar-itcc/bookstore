@php $editing = $category->exists; @endphp

<x-admin.layout :title="$editing ? 'Edit category' : 'Add category'">
    <x-admin.page-header :title="$editing ? 'Edit category' : 'Add category'"
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Categories' => route('admin.categories.index'), ($editing ? $category->name : 'Add') => null]" />

    <form method="POST" enctype="multipart/form-data"
          action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
          class="grid grid-cols-12 gap-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="admin-card col-span-12 space-y-5 p-6 lg:col-span-8">
            <h2 class="text-lg font-semibold text-navy">Category details</h2>
            <x-admin.input name="name" label="Name" :value="$category->name" required maxlength="120" placeholder="Fiction" />
            <x-admin.input name="slug" label="URL slug" :value="$category->slug" placeholder="fiction"
                           hint="Used in the web address. Leave empty to create it from the name." />
            <x-admin.select name="parent_id" label="Parent category" :options="$parents" :value="$category->parent_id"
                            placeholder="None (top-level category)" />
            <x-admin.textarea name="description" label="Description" :value="$category->description" rows="4" />
        </div>

        <div class="col-span-12 space-y-6 lg:col-span-4">
            <div class="admin-card space-y-5 p-6">
                <h2 class="text-lg font-semibold text-navy">Settings</h2>
                <x-admin.toggle name="is_active" label="Active" hint="Show this category in the store." :checked="$category->is_active" />
                <x-admin.input name="sort_order" type="number" min="0" label="Sort order" :value="$category->sort_order ?? 0"
                               hint="Lower numbers show first." />
            </div>

            <div class="admin-card p-6">
                @include('admin.partials.image-upload', [
                    'name' => 'image', 'label' => 'Image', 'removeName' => 'remove_image',
                    'current' => $category->image ? Storage::disk('public')->url($category->image) : null,
                ])
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1">{{ $editing ? 'Save changes' : 'Create category' }}</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</x-admin.layout>
