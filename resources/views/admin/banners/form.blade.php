@php $editing = $banner->exists; @endphp

<x-admin.layout :title="$editing ? 'Edit banner' : 'Add banner'">
    <x-admin.page-header :title="$editing ? 'Edit banner' : ($banner->placement === 'promo' ? 'Add offer box' : 'Add slide')"
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Banners & slider' => route('admin.banners.index'), ($editing ? Str::limit($banner->title, 30) : 'Add') => null]" />

    <form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.banners.update', $banner) : route('admin.banners.store') }}"
          class="grid grid-cols-12 gap-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="admin-card col-span-12 space-y-5 p-6 lg:col-span-7">
            <x-admin.select name="placement" label="Where it shows" :options="\App\Models\Banner::PLACEMENTS" :value="$banner->placement" required />
            <x-admin.input name="eyebrow" label="Small label" :value="$banner->eyebrow" maxlength="60" placeholder="New arrivals" hint="Optional line above the heading." />
            <x-admin.input name="title" label="Heading" :value="$banner->title" required maxlength="150" placeholder="Your favourite books, all in one place" />
            <x-admin.textarea name="subtitle" label="Text" :value="$banner->subtitle" rows="3" />
            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.input name="button_text" label="Button text" :value="$banner->button_text" maxlength="60" placeholder="Shop now" />
                <x-admin.input name="button_link" label="Button link" :value="$banner->button_link" placeholder="/books" hint="A page on your store (/books) or a full URL." />
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.input name="sort_order" type="number" min="0" label="Order" :value="$banner->sort_order" hint="Lower numbers show first." />
                <div class="pt-7"><x-admin.toggle name="is_active" label="Show on the store" :checked="$banner->is_active" /></div>
            </div>
        </div>

        <div class="col-span-12 space-y-6 lg:col-span-5">
            <div class="admin-card p-6">
                @include('admin.partials.image-upload', [
                    'name' => 'image', 'label' => 'Background image', 'removeName' => 'remove_image', 'maxMb' => 4,
                    'aspect' => $banner->placement === 'promo' ? 'aspect-[3/4] max-w-56' : 'aspect-[16/7]',
                    'current' => $banner->imageUrl(),
                    'hint' => $banner->placement === 'promo'
                        ? 'Portrait image. Optional.'
                        : 'Wide image, about 1920×840. Optional: without one, the slide shows your brand colours with featured book covers.',
                ])
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1">{{ $editing ? 'Save changes' : 'Create' }}</button>
                <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</x-admin.layout>
