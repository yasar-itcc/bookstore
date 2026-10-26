<x-admin.layout title="Contact messages">
    <x-admin.page-header title="Contact messages" subtitle="Messages sent from the Contact page."
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Content' => null]" />

    <div class="admin-card overflow-hidden">
        <form method="GET" class="flex flex-wrap items-center gap-3 border-b border-line p-5">
            <div class="relative min-w-56 flex-1 sm:max-w-xs">
                <i class="ti ti-search pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-muted"></i>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name, email, subject" class="admin-input pl-9">
            </div>
            <select name="status" class="admin-input w-auto" aria-label="Status">
                <option value="">All messages</option>
                <option value="unread" @selected(request('status') === 'unread')>Unread only</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
        </form>

        @if ($messages->isEmpty())
            <x-admin.empty-state icon="ti-inbox" title="No messages" text="When customers use the Contact page, their messages arrive here." />
        @else
            <div class="divide-y divide-line">
                @foreach ($messages as $message)
                    <a href="{{ route('admin.messages.show', $message) }}" @class(['flex items-start gap-4 px-5 py-4 hover:bg-page/60', 'bg-primary-soft/30' => ! $message->read_at])>
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-soft font-semibold text-primary">{{ Str::substr($message->name, 0, 1) }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center gap-2">
                                <span @class(['text-navy', 'font-semibold' => ! $message->read_at])>{{ $message->name }}</span>
                                <span class="truncate text-xs text-muted">{{ $message->email }}</span>
                                @unless ($message->read_at)<span class="size-2 rounded-full bg-cyan" title="Unread"></span>@endunless
                            </span>
                            <span @class(['block truncate text-sm', 'font-medium text-ink' => ! $message->read_at, 'text-muted' => $message->read_at])>{{ $message->subject ?: 'No subject' }}</span>
                            <span class="block truncate text-sm text-muted">{{ Str::limit($message->message, 120) }}</span>
                        </span>
                        <span class="shrink-0 text-xs text-muted">{{ $message->created_at->diffForHumans() }}</span>
                    </a>
                @endforeach
            </div>
            @include('admin.partials.pagination', ['paginator' => $messages])
        @endif
    </div>
</x-admin.layout>
