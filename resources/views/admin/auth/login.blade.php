@php
    $storeName = setting('store.name');
    $logo = \App\Models\Setting::imageUrl('store.logo');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · {{ $storeName }} Admin</title>
    @fonts('public-sans')
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="min-h-screen bg-white font-sans text-ink antialiased">
<div class="flex min-h-screen">
    {{-- Illustration side (hidden on small screens) --}}
    <div class="relative hidden flex-1 items-center justify-center overflow-hidden bg-page p-12 lg:flex">
        <div class="absolute -top-24 -left-24 size-72 rounded-full bg-cyan-soft"></div>
        <div class="absolute -right-16 -bottom-16 size-80 rounded-full bg-primary-soft"></div>
        <div class="relative text-center">
            <div class="mx-auto mb-8 flex size-40 items-center justify-center rounded-[2rem] bg-white shadow-card">
                <i class="ti ti-books text-[96px] text-primary"></i>
            </div>
            <p class="text-2xl font-semibold text-navy">Manage your bookstore</p>
            <p class="mt-2 text-muted">Books, categories, authors and stock, all in one place.</p>
        </div>
    </div>

    {{-- Form side --}}
    <div class="flex w-full items-center justify-center p-6 sm:p-12 lg:w-[480px] lg:shrink-0">
        <div class="w-full max-w-sm">
            <div class="mb-8 flex items-center gap-2.5">
                @if ($logo)
                    <img src="{{ $logo }}" alt="{{ $storeName }}" class="h-20 w-auto max-w-60 object-contain">
                @else
                    <span class="flex size-10 items-center justify-center rounded-lg bg-primary text-white"><i class="ti ti-book text-2xl"></i></span>
                    <span class="text-2xl font-bold text-navy">{{ $storeName }}</span>
                @endif
            </div>

            <h1 class="text-2xl font-semibold text-navy">Welcome back</h1>
            <p class="mt-1 mb-6 text-sm text-muted">Sign in to the admin panel.</p>

            @if ($errors->any())
                <div class="mb-5 flex items-start gap-2 rounded-md bg-amber-50 px-4 py-3 text-sm text-amber-800" role="alert">
                    <i class="ti ti-alert-triangle mt-0.5 text-lg"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-4" x-data="{ show: false }">
                @csrf
                <div>
                    <label for="email" class="admin-label">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           placeholder="you@example.com" class="admin-input">
                </div>
                <div>
                    <label for="password" class="admin-label">Password</label>
                    <div class="relative">
                        <input id="password" :type="show ? 'text' : 'password'" type="password" name="password" required autocomplete="current-password"
                               placeholder="············" class="admin-input pr-10">
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-muted hover:text-ink"
                                :aria-label="show ? 'Hide password' : 'Show password'">
                            <i class="ti text-lg" :class="show ? 'ti-eye-off' : 'ti-eye'"></i>
                        </button>
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm text-ink">
                    <input type="checkbox" name="remember" value="1" class="size-4 accent-primary">
                    Remember me
                </label>
                <button type="submit" class="btn btn-primary w-full py-2.5">Sign in</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
