<x-store.layout title="Pricing" :description="setting('pricing.text')">
    <x-store.page-hero title="Pricing" :crumbs="['Pricing' => null]" />

    <section class="store-container py-20">
        <div class="mx-auto mb-14 max-w-2xl text-center">
            <p class="mb-3 text-sm font-semibold tracking-[0.2em] text-muted uppercase">{{ setting('pricing.eyebrow') }}</p>
            <h2 class="section-title">{{ setting('pricing.title') }}</h2>
            <p class="mt-4 leading-relaxed text-muted">{{ setting('pricing.text') }}</p>
        </div>

        @if ($plans->isEmpty())
            <p class="text-center text-muted">Plans are coming soon.</p>
        @else
            <div @class([
                'mx-auto grid max-w-6xl items-stretch gap-8 md:grid-cols-2',
                'lg:grid-cols-3' => $plans->count() >= 3,
                'max-w-4xl' => $plans->count() < 3,
            ])>
                @foreach ($plans as $plan)
                    <div @class([
                        'relative flex flex-col rounded-2xl p-8 transition',
                        'bg-gradient-to-b from-navy to-primary text-white shadow-book lg:-translate-y-4' => $plan->is_highlighted,
                        'border border-line bg-white shadow-soft' => ! $plan->is_highlighted,
                    ])>
                        @if ($plan->is_highlighted)
                            <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-leaf px-4 py-1 text-xs font-bold tracking-wide text-white uppercase">Most popular</span>
                        @endif
                        <h3 @class(['font-serif text-2xl', 'text-navy' => ! $plan->is_highlighted])>{{ $plan->name }}</h3>
                        <p @class(['mt-2 text-sm', 'text-white/75' => $plan->is_highlighted, 'text-muted' => ! $plan->is_highlighted])>{{ $plan->description }}</p>
                        <p class="my-6 flex items-baseline gap-2">
                            <span @class(['text-5xl font-bold', 'text-navy' => ! $plan->is_highlighted])>{{ money($plan->price) }}</span>
                            <span @class(['text-sm', 'text-white/75' => $plan->is_highlighted, 'text-muted' => ! $plan->is_highlighted])>{{ \App\Models\PricingPlan::PERIODS[$plan->period] }}</span>
                        </p>
                        <ul class="mb-8 flex-1 space-y-3 text-sm">
                            @foreach ($plan->features ?? [] as $feature)
                                <li class="flex gap-3"><i @class(['ti ti-circle-check text-lg', 'text-leaf' => ! $plan->is_highlighted, 'text-cyan' => $plan->is_highlighted])></i>{{ $feature }}</li>
                            @endforeach
                        </ul>
                        @if ($plan->button_text)
                            <a href="{{ url($plan->button_link ?: '/contact') }}" @class([
                                'inline-flex items-center justify-center rounded-md px-6 py-3 text-sm font-semibold tracking-wide uppercase transition',
                                'bg-white text-primary hover:bg-primary-soft' => $plan->is_highlighted,
                                'bg-primary text-white hover:bg-primary-dark' => ! $plan->is_highlighted,
                            ])>{{ $plan->button_text }}</a>
                        @endif
                    </div>
                @endforeach
            </div>
            @if (setting('pricing.note'))
                <p class="mt-10 text-center text-sm text-muted"><i class="ti ti-info-circle"></i> {{ setting('pricing.note') }}</p>
            @endif
        @endif
    </section>

    <x-store.newsletter />
</x-store.layout>
