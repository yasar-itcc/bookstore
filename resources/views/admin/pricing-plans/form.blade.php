@php $editing = $plan->exists; @endphp

<x-admin.layout :title="$editing ? 'Edit plan' : 'Add plan'">
    <x-admin.page-header :title="$editing ? 'Edit plan' : 'Add plan'"
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Pricing plans' => route('admin.pricing-plans.index'), ($editing ? $plan->name : 'Add') => null]" />

    <form method="POST" action="{{ $editing ? route('admin.pricing-plans.update', $plan) : route('admin.pricing-plans.store') }}" class="grid grid-cols-12 gap-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="admin-card col-span-12 space-y-5 p-6 lg:col-span-8">
            <x-admin.input name="name" label="Plan name" :value="$plan->name" required maxlength="80" placeholder="Bookworm" />
            <x-admin.input name="description" label="Short description" :value="$plan->description" maxlength="300" placeholder="For readers who buy a book or two every month" />
            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.input name="price" type="number" step="0.01" min="0" label="Price (₹)" :value="$plan->price" required />
                <x-admin.select name="period" label="Billed" :options="\App\Models\PricingPlan::PERIODS" :value="$plan->period" required />
            </div>
            <x-admin.textarea name="features_text" label="What's included" :value="implode(PHP_EOL, $plan->features ?? [])" rows="7"
                              hint="One item per line, e.g. “10% off every order”." />
        </div>

        <div class="col-span-12 space-y-6 lg:col-span-4">
            <div class="admin-card space-y-5 p-6">
                <x-admin.input name="button_text" label="Button text" :value="$plan->button_text" placeholder="Choose plan" />
                <x-admin.input name="button_link" label="Button link" :value="$plan->button_link" placeholder="/contact" />
                <x-admin.input name="sort_order" type="number" min="0" label="Order" :value="$plan->sort_order" />
                <x-admin.toggle name="is_highlighted" label="Highlight as most popular" :checked="$plan->is_highlighted" />
                <x-admin.toggle name="is_active" label="Show on the Pricing page" :checked="$plan->is_active" />
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1">{{ $editing ? 'Save changes' : 'Create plan' }}</button>
                <a href="{{ route('admin.pricing-plans.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</x-admin.layout>
