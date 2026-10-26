<x-admin.layout title="Testimonials">
    <x-admin.page-header title="Testimonials" subtitle="Reader reviews shown in “What readers say” on the home and About pages."
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Content' => null]">
        <x-slot:actions>
            <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add testimonial</a>
        </x-slot:actions>
    </x-admin.page-header>

    @if ($testimonials->isEmpty())
        <div class="admin-card"><x-admin.empty-state icon="ti-message-heart" title="No testimonials yet">
            <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add testimonial</a>
        </x-admin.empty-state></div>
    @else
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($testimonials as $testimonial)
                <div class="admin-card flex flex-col p-6">
                    <div class="mb-3 text-lg tracking-wider" aria-label="{{ $testimonial->rating }} out of 5 stars">@for ($i = 1; $i <= 5; $i++)<span @class(['text-amber-500' => $i <= $testimonial->rating, 'text-slate-300' => $i > $testimonial->rating])>★</span>@endfor</div>
                    <p class="mb-4 flex-1 text-sm text-ink">“{{ $testimonial->quote }}”</p>
                    <div class="flex items-center gap-3">
                        @if ($testimonial->photoUrl())
                            <img src="{{ $testimonial->photoUrl() }}" alt="" class="size-10 rounded-full object-cover">
                        @else
                            <span class="flex size-10 items-center justify-center rounded-full bg-primary-soft font-semibold text-primary">{{ Str::substr($testimonial->name, 0, 1) }}</span>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-navy">{{ $testimonial->name }}</p>
                            <p class="text-xs text-muted">{{ $testimonial->role }}</p>
                        </div>
                        <x-admin.row-actions :edit="route('admin.testimonials.edit', $testimonial)" :destroy="route('admin.testimonials.destroy', $testimonial)" :name="$testimonial->name" />
                    </div>
                    <div class="mt-3 flex items-center gap-2 border-t border-line pt-3">
                        <x-admin.status-badge :active="$testimonial->is_active" on="Showing" off="Hidden" />
                        <span class="ml-auto text-xs text-muted">Order {{ $testimonial->sort_order }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-admin.layout>
