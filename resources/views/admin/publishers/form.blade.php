@php $editing = $publisher->exists; @endphp

<x-admin.layout :title="$editing ? 'Edit publisher' : 'Add publisher'">
    <x-admin.page-header :title="$editing ? 'Edit publisher' : 'Add publisher'"
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Publishers' => route('admin.publishers.index'), ($editing ? $publisher->name : 'Add') => null]" />

    <form method="POST" action="{{ $editing ? route('admin.publishers.update', $publisher) : route('admin.publishers.store') }}"
          class="admin-card max-w-3xl space-y-5 p-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.input name="name" label="Name" :value="$publisher->name" required maxlength="120" placeholder="Penguin Random House India" />
        <x-admin.input name="slug" label="URL slug" :value="$publisher->slug" hint="Leave empty to create it from the name." />
        <x-admin.input name="website" type="url" label="Website" :value="$publisher->website" placeholder="https://example.com" />
        <x-admin.toggle name="is_active" label="Active" :checked="$publisher->is_active" />

        <div class="flex gap-2 border-t border-line pt-5">
            <button type="submit" class="btn btn-primary">{{ $editing ? 'Save changes' : 'Create publisher' }}</button>
            <a href="{{ route('admin.publishers.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</x-admin.layout>
