<x-admin.layout title="Message from {{ $message->name }}">
    <x-admin.page-header :title="$message->subject ?: 'Message from '.$message->name"
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Contact messages' => route('admin.messages.index'), $message->name => null]">
        <x-slot:actions>
            <form method="POST" action="{{ route('admin.messages.update', $message) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="read" value="0">
                <button type="submit" class="btn btn-secondary"><i class="ti ti-mail"></i> Mark unread</button>
            </form>
            <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm('Delete this message?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-secondary"><i class="ti ti-trash"></i> Delete</button>
            </form>
            <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.($message->subject ?: 'Your message')) }}" class="btn btn-primary"><i class="ti ti-send"></i> Reply by email</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid grid-cols-12 gap-6">
        <div class="admin-card col-span-12 p-6 lg:col-span-8">
            <p class="text-sm leading-relaxed whitespace-pre-line text-ink">{{ $message->message }}</p>
        </div>
        <div class="admin-card col-span-12 space-y-4 p-6 text-sm lg:col-span-4">
            <div><p class="text-xs text-muted">From</p><p class="font-medium text-navy">{{ $message->name }}</p></div>
            <div><p class="text-xs text-muted">Email</p><a href="mailto:{{ $message->email }}" class="text-primary hover:underline">{{ $message->email }}</a></div>
            @if ($message->phone)
                <div><p class="text-xs text-muted">Phone</p><a href="tel:{{ $message->phone }}" class="text-primary hover:underline">{{ $message->phone }}</a></div>
            @endif
            <div><p class="text-xs text-muted">Received</p><p>{{ $message->created_at->format('d M Y, h:i A') }}</p></div>
        </div>
    </div>
</x-admin.layout>
