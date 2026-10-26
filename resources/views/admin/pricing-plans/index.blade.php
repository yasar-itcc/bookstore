<x-admin.layout title="Pricing plans">
    <x-admin.page-header title="Pricing plans" subtitle="Membership plans shown on the Pricing page. Page text is under Content → Pages → Pricing page."
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Marketing' => null]">
        <x-slot:actions>
            <a href="{{ url('/pricing') }}" target="_blank" class="btn btn-secondary"><i class="ti ti-external-link"></i> View page</a>
            <a href="{{ route('admin.pricing-plans.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add plan</a>
        </x-slot:actions>
    </x-admin.page-header>

    @if ($plans->isEmpty())
        <div class="admin-card"><x-admin.empty-state icon="ti-crown" title="No plans yet">
            <a href="{{ route('admin.pricing-plans.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add plan</a>
        </x-admin.empty-state></div>
    @else
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($plans as $plan)
                <div @class(['admin-card flex flex-col p-6', 'ring-2 ring-primary' => $plan->is_highlighted])>
                    <div class="mb-3 flex items-start justify-between gap-2">
                        <div>
                            <h2 class="text-lg font-semibold text-navy">{{ $plan->name }}</h2>
                            <p class="text-sm text-muted">{{ $plan->description }}</p>
                        </div>
                        <x-admin.row-actions :edit="route('admin.pricing-plans.edit', $plan)" :destroy="route('admin.pricing-plans.destroy', $plan)" :name="$plan->name" />
                    </div>
                    <p class="mb-4"><span class="text-3xl font-bold text-navy">{{ money($plan->price) }}</span> <span class="text-sm text-muted">{{ \App\Models\PricingPlan::PERIODS[$plan->period] }}</span></p>
                    <ul class="mb-4 flex-1 space-y-1.5 text-sm">
                        @foreach ($plan->features ?? [] as $feature)
                            <li class="flex gap-2"><i class="ti ti-check mt-0.5 text-leaf"></i>{{ $feature }}</li>
                        @endforeach
                    </ul>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-admin.status-badge :active="$plan->is_active" on="Showing" off="Hidden" />
                        @if ($plan->is_highlighted)<span class="rounded bg-primary-soft px-2 py-0.5 text-xs font-medium text-primary">Highlighted</span>@endif
                        <span class="ml-auto text-xs text-muted">Order {{ $plan->sort_order }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-admin.layout>
