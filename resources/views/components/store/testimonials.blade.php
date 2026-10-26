@props(['testimonials', 'title'])

@if ($testimonials->isNotEmpty())
    <section class="store-container py-16">
        <x-store.section-heading :title="$title" />
        <div class="grid gap-6 md:grid-cols-3">
            @foreach ($testimonials as $testimonial)
                <figure class="flex flex-col rounded-xl border border-line bg-white p-7 text-center shadow-soft">
                    <div class="mb-4 text-xl tracking-wider" aria-label="{{ $testimonial->rating }} out of 5 stars">@for ($i = 1; $i <= 5; $i++)<span @class(['text-amber-400' => $i <= $testimonial->rating, 'text-slate-200' => $i > $testimonial->rating])>★</span>@endfor</div>
                    <blockquote class="mb-6 flex-1 leading-relaxed text-ink">“{{ $testimonial->quote }}”</blockquote>
                    <figcaption class="flex flex-col items-center">
                        @if ($testimonial->photoUrl())
                            <img src="{{ $testimonial->photoUrl() }}" alt="" class="mb-3 size-14 rounded-full object-cover">
                        @else
                            <span class="mb-3 flex size-14 items-center justify-center rounded-full bg-primary-soft text-lg font-semibold text-primary">{{ Str::substr($testimonial->name, 0, 1) }}</span>
                        @endif
                        <span class="font-semibold text-navy">{{ $testimonial->name }}</span>
                        <span class="text-sm text-muted">{{ $testimonial->role }}</span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </section>
@endif
