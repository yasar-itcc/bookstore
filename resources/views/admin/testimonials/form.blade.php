@php $editing = $testimonial->exists; @endphp

<x-admin.layout :title="$editing ? 'Edit testimonial' : 'Add testimonial'">
    <x-admin.page-header :title="$editing ? 'Edit testimonial' : 'Add testimonial'"
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Testimonials' => route('admin.testimonials.index'), ($editing ? $testimonial->name : 'Add') => null]" />

    <form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}" class="grid grid-cols-12 gap-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="admin-card col-span-12 space-y-5 p-6 lg:col-span-8">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.input name="name" label="Name" :value="$testimonial->name" required maxlength="100" placeholder="Priya Nair" />
                <x-admin.input name="role" label="Who they are" :value="$testimonial->role" maxlength="100" placeholder="Teacher, Kochi" />
            </div>
            <x-admin.textarea name="quote" label="What they said" :value="$testimonial->quote" rows="5" required />
            <x-admin.select name="rating" label="Stars" :options="[5 => '★★★★★ (5)', 4 => '★★★★ (4)', 3 => '★★★ (3)', 2 => '★★ (2)', 1 => '★ (1)']" :value="$testimonial->rating" required />
            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.input name="sort_order" type="number" min="0" label="Order" :value="$testimonial->sort_order" />
                <div class="pt-7"><x-admin.toggle name="is_active" label="Show on the store" :checked="$testimonial->is_active" /></div>
            </div>
        </div>
        <div class="col-span-12 space-y-6 lg:col-span-4">
            <div class="admin-card p-6">
                @include('admin.partials.image-upload', ['name' => 'photo', 'label' => 'Photo', 'removeName' => 'remove_photo', 'aspect' => 'aspect-square max-w-40', 'current' => $testimonial->photoUrl(), 'hint' => 'Optional. Initials are shown without a photo.'])
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1">{{ $editing ? 'Save changes' : 'Create' }}</button>
                <a href="{{ route('admin.testimonials.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</x-admin.layout>
