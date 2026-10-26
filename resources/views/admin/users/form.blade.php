@use('App\Enums\Role')

@php $editing = $user->exists; @endphp

<x-admin.layout :title="$editing ? 'Edit admin user' : 'Add admin user'">
    <x-admin.page-header :title="$editing ? 'Edit '.$user->name : 'Add admin user'"
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Admin users' => route('admin.users.index'), ($editing ? $user->name : 'Add') => null]" />

    <form method="POST" action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}" class="admin-card max-w-3xl space-y-5 p-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <x-admin.input name="name" label="Name" :value="$user->name" required maxlength="100" />
            <x-admin.input name="email" type="email" label="Email" :value="$user->email" required autocomplete="off" />
        </div>

        <x-admin.field label="Role" name="role" required>
            <div class="grid gap-3 sm:grid-cols-3">
                @foreach (Role::cases() as $role)
                    <label class="flex cursor-pointer flex-col gap-1 rounded-md border border-line p-3 has-checked:border-primary has-checked:bg-primary-soft/40">
                        <span class="flex items-center gap-2 font-medium text-navy">
                            <input type="radio" name="role" value="{{ $role->value }}" class="accent-primary" @checked(old('role', $user->role?->value) === $role->value)>
                            {{ $role->label() }}
                        </span>
                        <span class="text-xs text-muted">{{ $role->description() }}</span>
                    </label>
                @endforeach
            </div>
        </x-admin.field>

        <div class="grid gap-5 sm:grid-cols-2">
            <x-admin.input name="password" type="password" :label="$editing ? 'New password' : 'Password'" :required="! $editing" autocomplete="new-password"
                           :hint="$editing ? 'Leave empty to keep the current password.' : 'At least 8 characters.'" />
            <x-admin.input name="password_confirmation" type="password" label="Repeat password" :required="! $editing" autocomplete="new-password" />
        </div>

        <div class="flex gap-2 border-t border-line pt-5">
            <button type="submit" class="btn btn-primary">{{ $editing ? 'Save changes' : 'Add user' }}</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</x-admin.layout>
