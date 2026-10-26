@use('App\Enums\Role')

<x-admin.layout title="Admin users">
    <x-admin.page-header title="Admin users" subtitle="People who can sign in to this admin panel, and what they can do."
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Settings' => null]">
        <x-slot:actions>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="ti ti-user-plus"></i> Add user</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mb-6 grid gap-4 md:grid-cols-3">
        @foreach (Role::cases() as $role)
            <div class="admin-card p-5">
                <p class="font-semibold text-navy">{{ $role->label() }}</p>
                <p class="text-sm text-muted">{{ $role->description() }}</p>
            </div>
        @endforeach
    </div>

    <div class="admin-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Added</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="flex size-9 items-center justify-center rounded-full bg-primary-soft font-semibold text-primary">{{ Str::substr($user->name, 0, 1) }}</span>
                                <span class="font-medium text-navy">{{ $user->name }}</span>
                                @if ($user->is(auth()->user()))<span class="rounded bg-page px-2 py-0.5 text-xs text-muted">You</span>@endif
                            </div>
                        </td>
                        <td class="text-muted">{{ $user->email }}</td>
                        <td>
                            <span @class([
                                'rounded px-2 py-0.5 text-xs font-medium',
                                'bg-primary-soft text-primary' => $user->role === Role::Admin,
                                'bg-cyan-soft text-cyan' => $user->role === Role::Manager,
                                'bg-slate-100 text-slate-600' => $user->role === Role::Staff || ! $user->role,
                            ])>{{ $user->role?->label() ?? 'Staff' }}</span>
                        </td>
                        <td class="text-muted">{{ $user->created_at->format('d M Y') }}</td>
                        <td>
                            <x-admin.row-actions :edit="route('admin.users.edit', $user)"
                                                 :destroy="$user->is(auth()->user()) ? null : route('admin.users.destroy', $user)" :name="$user->name.' (admin access)'" />
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin.layout>
