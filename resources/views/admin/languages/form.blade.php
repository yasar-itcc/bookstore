@php $editing = $language->exists; @endphp

<x-admin.layout :title="$editing ? 'Edit language' : 'Add language'">
    <x-admin.page-header :title="$editing ? 'Edit language' : 'Add language'"
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Languages' => route('admin.languages.index'), ($editing ? $language->name : 'Add') => null]" />

    <form method="POST" action="{{ $editing ? route('admin.languages.update', $language) : route('admin.languages.store') }}"
          class="admin-card max-w-3xl space-y-5 p-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.input name="name" label="Name" :value="$language->name" required maxlength="60" placeholder="Kannada" />
        <x-admin.input name="code" label="Code" :value="$language->code" required maxlength="10" placeholder="kn"
                       hint="Short language code, e.g. en, hi, ur." />
        <x-admin.toggle name="is_active" label="Active" :checked="$language->is_active" />

        <div class="flex gap-2 border-t border-line pt-5">
            <button type="submit" class="btn btn-primary">{{ $editing ? 'Save changes' : 'Create language' }}</button>
            <a href="{{ route('admin.languages.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</x-admin.layout>
