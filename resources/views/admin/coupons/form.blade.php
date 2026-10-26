@php $editing = $coupon->exists; @endphp

<x-admin.layout :title="$editing ? 'Edit coupon' : 'Add coupon'">
    <x-admin.page-header :title="$editing ? 'Edit coupon '.$coupon->code : 'Add coupon'"
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Coupons' => route('admin.coupons.index'), ($editing ? $coupon->code : 'Add') => null]" />

    <form method="POST" action="{{ $editing ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}"
          class="grid grid-cols-12 gap-6" x-data="{ type: @js(old('type', $coupon->type)) }">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="admin-card col-span-12 space-y-5 p-6 lg:col-span-8">
            <h2 class="text-lg font-semibold text-navy">Discount</h2>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.input name="code" label="Code" :value="$coupon->code" required maxlength="40" placeholder="WELCOME10" class="font-mono uppercase"
                               hint="Letters, numbers, - and _. Customers type this at checkout." />
                <x-admin.input name="description" label="Description" :value="$coupon->description" placeholder="10% off your first order" hint="For your reference; may be shown to customers." />
            </div>
            <div class="grid gap-5 sm:grid-cols-3">
                <x-admin.field label="Type" name="type" required>
                    <select name="type" id="type" x-model="type" class="admin-input">
                        @foreach (\App\Models\Coupon::TYPES as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', $coupon->type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </x-admin.field>
                <x-admin.input name="value" type="number" step="0.01" min="0" label="Value" :value="$coupon->value" required placeholder="10" />
                <div x-show="type === 'percent'">
                    <x-admin.input name="max_discount" type="number" step="0.01" min="0" label="Maximum discount (₹)" :value="$coupon->max_discount" hint="Optional cap, e.g. ₹200." />
                </div>
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.input name="min_order_amount" type="number" step="0.01" min="0" label="Minimum order (₹)" :value="$coupon->min_order_amount" hint="0 for no minimum." />
                <x-admin.input name="usage_limit" type="number" min="1" label="Total uses allowed" :value="$coupon->usage_limit" hint="Leave empty for unlimited." />
            </div>
        </div>

        <div class="col-span-12 space-y-6 lg:col-span-4">
            <div class="admin-card space-y-5 p-6">
                <h2 class="text-lg font-semibold text-navy">Validity</h2>
                <x-admin.input name="starts_at" type="datetime-local" label="Starts" :value="$coupon->starts_at?->format('Y-m-d\TH:i')" hint="Leave empty to start now." />
                <x-admin.input name="expires_at" type="datetime-local" label="Ends" :value="$coupon->expires_at?->format('Y-m-d\TH:i')" hint="Leave empty for no end date." />
                <x-admin.toggle name="is_active" label="Active" :checked="$coupon->is_active" />
                @if ($editing)
                    <p class="text-sm text-muted">Used {{ $coupon->used_count }} {{ Str::plural('time', $coupon->used_count) }} so far.</p>
                @endif
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary flex-1">{{ $editing ? 'Save changes' : 'Create coupon' }}</button>
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</x-admin.layout>
