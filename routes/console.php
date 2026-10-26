<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:create', function () {
    $email = text('Admin email', required: true, validate: fn ($value) => Validator::make(['email' => $value], ['email' => 'email'])->fails() ? 'Enter a valid email address.' : null);
    $existing = User::where('email', $email)->first();
    $name = text('Name', default: $existing->name ?? 'Admin', required: true);
    $password = password('Password (min 8 characters)', required: true, validate: fn ($value) => strlen($value) < 8 ? 'Use at least 8 characters.' : null);

    $user = User::updateOrCreate(['email' => $email], ['name' => $name, 'password' => $password]);
    $user->forceFill(['is_admin' => true, 'role' => 'admin'])->save();

    $this->info(($existing ? 'Updated' : 'Created')." admin {$email}. Sign in at ".route('admin.login'));
})->purpose('Create an admin user, or reset an existing user\'s password and make them an admin');
