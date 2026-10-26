<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_the_admin_login(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->get('/admin/login')->assertOk()->assertSee('Sign in');
    }

    public function test_an_admin_can_sign_in_and_see_the_dashboard(): void
    {
        $admin = User::factory()->create(['password' => 'secret-pass']);
        $admin->forceFill(['is_admin' => true])->save();

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'secret-pass'])
            ->assertRedirect(route('admin.dashboard'));

        $this->get('/admin')->assertOk()->assertSee('Catalog statistics');
    }

    public function test_a_wrong_password_is_rejected(): void
    {
        $admin = User::factory()->create(['password' => 'secret-pass']);
        $admin->forceFill(['is_admin' => true])->save();

        $this->from('/admin/login')
            ->post('/admin/login', ['email' => $admin->email, 'password' => 'wrong'])
            ->assertRedirect('/admin/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_a_non_admin_user_cannot_sign_in_or_open_the_admin(): void
    {
        $customer = User::factory()->create(['password' => 'secret-pass']);

        $this->post('/admin/login', ['email' => $customer->email, 'password' => 'secret-pass'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($customer)->get('/admin')->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_is_admin_cannot_be_mass_assigned(): void
    {
        $user = User::create(['name' => 'X', 'email' => 'x@example.com', 'password' => 'secret-pass', 'is_admin' => true]);

        $this->assertFalse((bool) $user->fresh()->is_admin);
    }

    public function test_every_admin_page_loads(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        foreach (['dashboard', 'books.index', 'books.create', 'categories.index', 'categories.create', 'authors.index', 'authors.create',
            'publishers.index', 'publishers.create', 'languages.index', 'languages.create'] as $route) {
            $this->actingAs($admin)->get(route("admin.{$route}"))->assertOk();
        }
    }

    public function test_the_admin_create_command_makes_an_admin(): void
    {
        $this->artisan('admin:create')
            ->expectsQuestion('Admin email', 'owner@example.com')
            ->expectsQuestion('Name', 'Owner')
            ->expectsQuestion('Password (min 8 characters)', 'long-enough-pass')
            ->assertSuccessful();

        $user = User::where('email', 'owner@example.com')->first();
        $this->assertTrue($user->is_admin);
    }
}
