<x-admin.layout title="Banners & slider">
    <x-admin.page-header title="Banners & slider" subtitle="Slides at the top of the home page, and the offer box beside the category columns."
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Marketing' => null]">
        <x-slot:actions>
            <a href="{{ route('admin.banners.create', ['placement' => 'promo']) }}" class="btn btn-secondary"><i class="ti ti-plus"></i> Add offer box</a>
            <a href="{{ route('admin.banners.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add slide</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="space-y-6">
        @foreach (\App\Models\Banner::PLACEMENTS as $placement => $label)
            <div class="admin-card overflow-hidden">
                <div class="flex items-center justify-between border-b border-line p-5">
                    <h2 class="text-lg font-semibold text-navy">{{ $label }}</h2>
                    <span class="text-sm text-muted">{{ $placement === 'hero' ? 'Shown in order; the slider rotates every few seconds.' : 'The first active one is shown.' }}</span>
                </div>
                @php $items = $banners[$placement] ?? collect(); @endphp
                @if ($items->isEmpty())
                    <x-admin.empty-state icon="ti-photo" :title="$placement === 'hero' ? 'No slides yet' : 'No offer box yet'"
                                         :text="$placement === 'hero' ? 'Without slides, the home page shows a welcome banner with your featured books.' : 'Without one, the category columns use the full width.'" />
                @else
                    <div class="divide-y divide-line">
                        @foreach ($items as $banner)
                            <div class="flex flex-wrap items-center gap-4 p-5">
                                <div class="flex h-20 w-36 shrink-0 items-center justify-center overflow-hidden rounded-md bg-gradient-to-br from-navy to-primary text-white">
                                    @if ($banner->imageUrl())
                                        <img src="{{ $banner->imageUrl() }}" alt="" class="size-full object-cover">
                                    @else
                                        <i class="ti ti-photo text-2xl opacity-60"></i>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    @if ($banner->eyebrow)<p class="text-xs font-semibold tracking-wide text-cyan uppercase">{{ $banner->eyebrow }}</p>@endif
                                    <a href="{{ route('admin.banners.edit', $banner) }}" class="font-semibold text-navy hover:text-primary">{{ $banner->title }}</a>
                                    <p class="truncate text-sm text-muted">{{ $banner->subtitle }}</p>
                                    @if ($banner->button_text)<p class="mt-1 text-xs text-muted">Button: {{ $banner->button_text }} → {{ $banner->button_link }}</p>@endif
                                </div>
                                <span class="text-sm text-muted">Order {{ $banner->sort_order }}</span>
                                <x-admin.status-badge :active="$banner->is_active" on="Showing" off="Hidden" />
                                <x-admin.row-actions :edit="route('admin.banners.edit', $banner)" :destroy="route('admin.banners.destroy', $banner)" :name="$banner->title" />
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</x-admin.layout>
