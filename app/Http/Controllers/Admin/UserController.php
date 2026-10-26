<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * People who can sign in to the admin panel, and their roles.
 */
class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', ['users' => User::where('is_admin', true)->orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User(['role' => Role::Staff])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $user = User::create($data);
        $user->forceFill(['is_admin' => true, 'role' => $data['role']])->save();

        return redirect()->route('admin.users.index')->with('status', "{$user->name} can now sign in as {$user->role->label()}.");
    }

    public function edit(User $user): View
    {
        abort_unless($user->is_admin, 404);

        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->is_admin, 404);
        $data = $this->validated($request, $user);

        if ($user->is($request->user()) && $data['role'] !== Role::Admin->value) {
            return back()->with('warning', 'You can\'t change your own role. Ask another admin to do it.');
        }

        $user->update(array_filter($data, fn ($value, $key) => $key !== 'password' || filled($value), ARRAY_FILTER_USE_BOTH));
        $user->forceFill(['role' => $data['role']])->save();

        return redirect()->route('admin.users.index')->with('status', "{$user->name} updated.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->is_admin, 404);

        if ($user->is($request->user())) {
            return back()->with('warning', 'You can\'t remove your own account.');
        }

        // Keep the user record (it may own blog posts) but take away admin access.
        $user->forceFill(['is_admin' => false, 'role' => null])->save();

        return redirect()->route('admin.users.index')->with('status', "{$user->name} can no longer sign in to the admin panel.");
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user)],
            'role' => ['required', new Enum(Role::class)],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)],
        ]);
    }
}
