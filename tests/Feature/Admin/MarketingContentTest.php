<?php

namespace Tests\Feature\Admin;

use App\Models\Banner;
use App\Models\Book;
use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\Post;
use App\Models\PricingPlan;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketingContentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $role = 'admin'): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true, 'role' => $role])->save();

        return $user;
    }

    public function test_every_new_admin_page_loads_for_an_admin(): void
    {
        $this->actingAs($this->admin());

        foreach (['coupons.index', 'coupons.create', 'banners.index', 'banners.create', 'highlights.index', 'subscribers.index',
            'pricing-plans.index', 'pricing-plans.create', 'pages.index', 'pages.create', 'posts.index', 'posts.create',
            'testimonials.index', 'testimonials.create', 'messages.index', 'users.index', 'users.create'] as $route) {
            $this->get(route("admin.{$route}"))->assertOk();
        }

        foreach (['store', 'email', 'seo'] as $group) {
            $this->get(route('admin.settings.edit', $group))->assertOk();
        }

        foreach (['home', 'about', 'pricing'] as $group) {
            $this->get(route('admin.page-content.edit', $group))->assertOk();
        }
        $this->get(route('admin.pages.index'))->assertSee('Home page')->assertSee('About page')->assertSee('Pricing page');
    }

    public function test_home_about_and_pricing_text_live_under_pages_not_settings(): void
    {
        $this->actingAs($this->admin('manager'));

        $this->get(route('admin.page-content.edit', 'home'))->assertOk()->assertSee('Announcement bar');
        $this->put(route('admin.page-content.update', 'home'), ['home__bestsellers_title' => 'Top picks this week', 'home__show_blog' => 0])
            ->assertRedirect(route('admin.page-content.edit', 'home'));
        $this->assertSame('Top picks this week', setting('home.bestsellers_title'));
        $this->assertSame('0', setting('home.show_blog'));

        // Each screen only edits its own groups.
        $this->actingAs($this->admin());
        $this->get(route('admin.settings.edit', 'home'))->assertNotFound();
        $this->put(route('admin.page-content.update', 'store'), ['store__name' => 'Hacked'])->assertNotFound();
        $this->get(route('admin.settings.edit'))->assertOk()->assertDontSee('Announcement bar');
    }

    public function test_roles_limit_what_each_user_can_open(): void
    {
        $this->actingAs($this->admin('manager'));
        $this->get(route('admin.coupons.index'))->assertOk();
        $this->get(route('admin.posts.index'))->assertOk();
        $this->get(route('admin.settings.edit'))->assertForbidden();
        $this->get(route('admin.users.index'))->assertForbidden();

        $this->actingAs($this->admin('staff'));
        $this->get(route('admin.books.index'))->assertOk();
        $this->get(route('admin.messages.index'))->assertOk();
        $this->get(route('admin.coupons.index'))->assertForbidden();
        $this->get(route('admin.pages.index'))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertOk()->assertDontSee('Coupons')->assertDontSee('Store settings');
    }

    public function test_coupons(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.coupons.store'), ['code' => ' welcome10 ', 'type' => 'percent', 'value' => 10, 'max_discount' => 200, 'is_active' => 1])
            ->assertRedirect(route('admin.coupons.index'));
        $coupon = Coupon::firstWhere('code', 'WELCOME10');
        $this->assertSame('Active', $coupon->status());
        $this->assertSame(100.0, $coupon->discountFor(1000));
        $this->assertSame(200.0, $coupon->discountFor(5000));

        $this->post(route('admin.coupons.store'), ['code' => 'TOOMUCH', 'type' => 'percent', 'value' => 150])->assertSessionHasErrors('value');
        $this->post(route('admin.coupons.store'), ['code' => 'WELCOME10', 'type' => 'fixed', 'value' => 50])->assertSessionHasErrors('code');

        $expired = Coupon::create(['code' => 'OLD', 'type' => 'fixed', 'value' => 50, 'expires_at' => now()->subDay(), 'is_active' => true]);
        $this->assertSame('Expired', $expired->status());
        $this->assertSame(0.0, $expired->discountFor(1000));
    }

    public function test_banners_highlights_subscribers_and_plans(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());

        $this->post(route('admin.banners.store'), [
            'placement' => 'hero', 'title' => 'Monsoon sale', 'button_text' => 'Shop', 'button_link' => '/books',
            'is_active' => 1, 'image' => UploadedFile::fake()->image('slide.jpg', 1920, 840),
        ])->assertRedirect(route('admin.banners.index'));
        Storage::disk('public')->assertExists(Banner::first()->image);

        $book = Book::factory()->create();
        $this->patch(route('admin.highlights.update', $book), ['field' => 'is_bestseller', 'value' => 1])->assertRedirect();
        $this->assertTrue($book->fresh()->is_bestseller);
        $this->patch(route('admin.highlights.update', $book), ['field' => 'title', 'value' => 1])->assertSessionHasErrors('field');

        NewsletterSubscriber::create(['email' => 'reader@example.com']);
        NewsletterSubscriber::create(['email' => 'gone@example.com', 'is_active' => false]);
        $csv = $this->get(route('admin.subscribers.export'))->assertOk()->streamedContent();
        $this->assertStringContainsString('reader@example.com', $csv);
        $this->assertStringNotContainsString('gone@example.com', $csv);

        $this->post(route('admin.pricing-plans.store'), [
            'name' => 'Bookworm', 'price' => 149, 'period' => 'month', 'features_text' => "10% off\n\n  Free delivery  \n", 'is_active' => 1,
        ])->assertRedirect();
        $this->assertSame(['10% off', 'Free delivery'], PricingPlan::first()->features);
    }

    public function test_pages_posts_testimonials_and_messages(): void
    {
        $this->actingAs($admin = $this->admin());

        $this->post(route('admin.pages.store'), ['title' => 'Shipping Policy', 'content' => "## Delivery\nFast.", 'is_published' => 1, 'show_in_footer' => 1])->assertRedirect();
        $this->assertSame('shipping-policy', Page::first()->slug);
        $this->post(route('admin.pages.store'), ['title' => 'Blog', 'slug' => 'blog'])->assertSessionHasErrors('slug');

        $this->post(route('admin.posts.store'), ['title' => 'Top 10 books', 'content' => 'Hello', 'category' => 'Reading Lists', 'is_published' => 1])->assertRedirect();
        $post = Post::first();
        $this->assertTrue($post->author->is($admin));
        $this->assertNotNull($post->published_at);

        $this->post(route('admin.testimonials.store'), ['name' => 'Priya', 'quote' => 'Great store', 'rating' => 5, 'is_active' => 1])->assertRedirect();
        $this->post(route('admin.testimonials.store'), ['name' => 'X', 'quote' => 'Hmm', 'rating' => 9])->assertSessionHasErrors('rating');
        $this->assertSame(1, Testimonial::count());

        $message = ContactMessage::create(['name' => 'Ravi', 'email' => 'ravi@example.com', 'message' => 'Do you have this book?']);
        $this->get(route('admin.messages.show', $message))->assertOk()->assertSee('Do you have this book?');
        $this->assertNotNull($message->fresh()->read_at);
    }

    public function test_settings_are_saved_including_lists_and_images(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());

        $this->put(route('admin.settings.update', 'store'), [
            'store__name' => 'Kitab Ghar', 'store__email' => 'hi@kitabghar.in', 'store__cod_enabled' => 0,
            'store__logo' => UploadedFile::fake()->image('logo.png', 200, 60),
        ])->assertRedirect(route('admin.settings.edit', 'store'));

        $this->assertSame('Kitab Ghar', setting('store.name'));
        $this->assertSame('0', setting('store.cod_enabled'));
        Storage::disk('public')->assertExists(setting('store.logo'));

        $this->put(route('admin.page-content.update', 'about'), [
            'about__features' => [['icon' => 'ti-gift', 'title' => 'Gift wrap', 'text' => 'Free'], ['icon' => '', 'title' => '', 'text' => '']],
            'about__team' => [['name' => 'Asha', 'role' => 'Founder', 'photo' => null]],
        ])->assertRedirect();
        $this->assertSame([['icon' => 'ti-gift', 'title' => 'Gift wrap', 'text' => 'Free']], Setting::json('about.features'));
        $this->assertSame('Asha', Setting::json('about.team')[0]['name']);

        $this->put(route('admin.settings.update', 'store'), ['store__name' => ''])->assertSessionHasErrors('store__name');
        $this->put(route('admin.page-content.update', 'about'), ['about__features' => [['icon' => '<script>', 'title' => 'x']]])->assertSessionHasErrors('about__features.0.icon');
    }

    public function test_uploaded_logos_and_favicon_are_shown_everywhere(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());

        // A 300×200 transparent PNG with a solid 100×50 block in the middle.
        $image = imagecreatetruecolor(300, 200);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        imagefilledrectangle($image, 100, 75, 199, 124, imagecolorallocate($image, 11, 37, 69));
        $file = tempnam(sys_get_temp_dir(), 'logo').'.png';
        imagepng($image, $file);

        $this->put(route('admin.settings.update', 'store'), [
            'store__name' => 'Book Store',
            'store__logo' => new UploadedFile($file, 'logo.png', 'image/png', null, true),
        ])->assertRedirect();

        [$width, $height] = getimagesize(Storage::disk('public')->path(setting('store.logo')));
        $this->assertSame([108, 58], [$width, $height]); // 100×50 artwork + 4px padding each side

        Setting::put(['store.favicon' => 'settings/icon.png']);
        $iconUrl = Storage::disk('public')->url('settings/icon.png');
        $this->get('/')->assertSee('<link rel="icon" type="image/png" href="'.$iconUrl.'">', false);
        $this->get(route('admin.dashboard'))->assertSee('<link rel="icon" type="image/png" href="'.$iconUrl.'">', false);

        $logoUrl = Storage::disk('public')->url(setting('store.logo'));
        $this->get('/')->assertSee($logoUrl);
        $this->get(route('admin.dashboard'))->assertSee($logoUrl);
        $this->post(route('admin.logout'));
        $this->get(route('admin.login'))->assertSee($logoUrl);
    }

    public function test_admin_users_and_roles(): void
    {
        $this->actingAs($me = $this->admin());

        $this->post(route('admin.users.store'), [
            'name' => 'Staff One', 'email' => 'staff@example.com', 'role' => 'staff', 'password' => 'long-password', 'password_confirmation' => 'long-password',
        ])->assertRedirect(route('admin.users.index'));
        $staff = User::firstWhere('email', 'staff@example.com');
        $this->assertTrue($staff->is_admin);
        $this->assertSame('staff', $staff->role->value);

        $this->delete(route('admin.users.destroy', $me))->assertSessionHas('warning');
        $this->put(route('admin.users.update', $me), ['name' => $me->name, 'email' => $me->email, 'role' => 'staff'])->assertSessionHas('warning');
        $this->assertSame('admin', $me->fresh()->role->value);

        $this->delete(route('admin.users.destroy', $staff))->assertRedirect();
        $this->assertFalse($staff->fresh()->is_admin);
    }
}
