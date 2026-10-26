<x-admin.layout title="Coupons">
    <x-admin.page-header title="Coupons" subtitle="Discount codes customers enter at checkout."
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Marketing' => null]">
        <x-slot:actions>
            <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add coupon</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="admin-card overflow-hidden">
        @include('admin.partials.search', ['placeholder' => 'Search by code'])

        @if ($coupons->isEmpty())
            <x-admin.empty-state icon="ti-discount-2" title="No coupons yet" text="Create a code like WELCOME10 for 10% off a first order.">
                <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Add coupon</a>
            </x-admin.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead><tr><th>Code</th><th>Discount</th><th>Minimum order</th><th>Used</th><th>Valid</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                    @foreach ($coupons as $coupon)
                        @php $status = $coupon->status(); @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.coupons.edit', $coupon) }}" class="rounded bg-primary-soft px-2 py-1 font-mono text-sm font-semibold text-primary">{{ $coupon->code }}</a>
                                @if ($coupon->description)<p class="mt-1 text-xs text-muted">{{ $coupon->description }}</p>@endif
                            </td>
                            <td class="font-medium text-navy">
                                {{ $coupon->label() }}
                                @if ($coupon->type === 'percent' && $coupon->max_discount)<span class="block text-xs font-normal text-muted">up to {{ money($coupon->max_discount) }}</span>@endif
                            </td>
                            <td>{{ (float) $coupon->min_order_amount ? money($coupon->min_order_amount) : '—' }}</td>
                            <td>{{ $coupon->used_count }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}</td>
                            <td class="text-xs text-muted">
                                {{ $coupon->starts_at?->format('d M Y') ?? 'Now' }} → {{ $coupon->expires_at?->format('d M Y') ?? 'No end' }}
                            </td>
                            <td>
                                <span @class([
                                    'rounded px-2 py-0.5 text-xs font-medium',
                                    'bg-leaf-soft text-leaf-dark' => $status === 'Active',
                                    'bg-cyan-soft text-cyan' => $status === 'Scheduled',
                                    'bg-amber-50 text-amber-700' => in_array($status, ['Expired', 'Used up']),
                                    'bg-slate-100 text-slate-500' => $status === 'Inactive',
                                ])>{{ $status }}</span>
                            </td>
                            <td><x-admin.row-actions :edit="route('admin.coupons.edit', $coupon)" :destroy="route('admin.coupons.destroy', $coupon)" :name="$coupon->code" /></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @include('admin.partials.pagination', ['paginator' => $coupons])
        @endif
    </div>
</x-admin.layout>
