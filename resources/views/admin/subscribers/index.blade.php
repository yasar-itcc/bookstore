<x-admin.layout title="Newsletter subscribers">
    <x-admin.page-header title="Newsletter subscribers" :subtitle="$activeCount.' active '.Str::plural('subscriber', $activeCount).'. People join from the newsletter form on your store.'"
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Marketing' => null]">
        <x-slot:actions>
            <a href="{{ route('admin.subscribers.export') }}" class="btn btn-primary"><i class="ti ti-download"></i> Download CSV</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="admin-card overflow-hidden">
        @include('admin.partials.search', ['placeholder' => 'Search email'])

        @if ($subscribers->isEmpty())
            <x-admin.empty-state icon="ti-mail" title="No subscribers yet" text="Visitors can subscribe from the newsletter box on the home page and footer." />
        @else
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead><tr><th>Email</th><th>Subscribed</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                    @foreach ($subscribers as $subscriber)
                        <tr>
                            <td class="font-medium text-navy">{{ $subscriber->email }}</td>
                            <td class="text-muted">{{ $subscriber->created_at->format('d M Y') }}</td>
                            <td><x-admin.status-badge :active="$subscriber->is_active" on="Subscribed" off="Unsubscribed" /></td>
                            <td>
                                <div class="flex justify-end gap-1">
                                    <form method="POST" action="{{ route('admin.subscribers.update', $subscriber) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="is_active" value="{{ $subscriber->is_active ? 0 : 1 }}">
                                        <button type="submit" class="flex size-8 items-center justify-center rounded-full text-muted hover:bg-page hover:text-primary"
                                                title="{{ $subscriber->is_active ? 'Unsubscribe' : 'Subscribe again' }}">
                                            <i class="ti {{ $subscriber->is_active ? 'ti-mail-off' : 'ti-mail-check' }} text-lg"></i>
                                        </button>
                                    </form>
                                    <x-admin.delete-button :action="route('admin.subscribers.destroy', $subscriber)" :name="$subscriber->email" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @include('admin.partials.pagination', ['paginator' => $subscribers])
        @endif
    </div>
</x-admin.layout>
