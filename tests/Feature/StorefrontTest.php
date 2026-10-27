<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Book;
use App\Models\BookFormat;
use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\Post;
use App\Models\PricingPlan;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DemoContentSeeder;
use Database\Seeders\SampleCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_page_loads_with_demo_content(): void
    {
        $this->seed([SampleCatalogSeeder::class, DemoContentSeeder::class]);

        $this->get('/')->assertOk()
            ->assertSee('Your favourite books, all in one place')
            ->assertSee(setting('home.bestsellers_title'))
            ->assertSee('What readers say')
            ->assertSee('Join our newsletter');

        $this->get('/books')->assertOk()->assertSee('Atomic Habits');
        $this->get('/books?category=hindi-books')->assertOk()->assertSee('Godaan')->assertDontSee('Atomic Habits');
        $this->get('/books?search=Laxmikanth')->assertOk()->assertSee('Indian Polity');
        $this->get('/books?format=ebook&sort=price_low')->assertOk();
        $this->get('/books/atomic-habits')->assertOk()->assertSee('James Clear')->assertSee('Choose a format');
        $this->get('/about')->assertOk()->assertSee(setting('about.intro_title'));
        $this->get('/pricing')->assertOk()->assertSee('Bookworm')->assertSee('Most popular');
        $this->get('/blog')->assertOk()->assertSee('10 Books Every Indian Student Should Read');
        $this->get('/blog?category=Author+Spotlight')->assertOk()->assertSee('Why Wings of Fire Still Inspires Millions');
        $this->get('/blog/'.Post::first()->slug)->assertOk();
        $this->get('/contact')->assertOk();
        $this->get('/shipping-policy')->assertOk()->assertSee('Delivery charges');
        $this->get('/faq')->assertOk();

        $this->assertSame(14, Post::count());
        $this->assertSame(3, PricingPlan::count());
    }

    public function test_home_sections_follow_settings_and_banners(): void
    {
        $book = Book::factory()->create(['title' => 'Bestselling Book', 'is_bestseller' => true]);
        BookFormat::factory()->for($book)->create();
        Banner::create(['placement' => 'hero', 'title' => 'Monsoon Reading Sale', 'is_active' => true]);
        Banner::create(['placement' => 'hero', 'title' => 'Hidden slide', 'is_active' => false]);

        $this->get('/')->assertSee('Monsoon Reading Sale')->assertDontSee('Hidden slide')->assertSee('Bestselling Book');

        Setting::put(['home.show_bestsellers' => '0', 'home.announcement_text' => 'Diwali offers are live']);

        $this->get('/')->assertDontSee(setting('home.bestsellers_title'))->assertSee('Diwali offers are live');
    }

    public function test_hidden_and_future_content_is_not_public(): void
    {
        $hidden = Book::factory()->create(['is_active' => false]);
        Page::create(['title' => 'Draft Page', 'content' => 'x', 'is_published' => false]);
        Post::create(['title' => 'Coming Soon Post', 'content' => 'x', 'is_published' => true, 'published_at' => now()->addWeek()]);

        $this->get('/books/'.$hidden->slug)->assertNotFound();
        $this->get('/draft-page')->assertNotFound();
        $this->get('/blog/coming-soon-post')->assertNotFound();
        $this->get('/blog')->assertDontSee('Coming Soon Post');
    }

    public function test_page_content_is_safe_html_and_site_links_work_in_a_subfolder(): void
    {
        $page = Page::create(['title' => 'Test', 'content' => "Hi <script>alert(1)</script>\n\n[Books](/books) and [JS](javascript:alert(1))", 'is_published' => true]);

        $html = $page->html();
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringContainsString('href="'.url('/books').'"', $html);
    }

    public function test_newsletter_signup(): void
    {
        $this->from('/')->post('/newsletter', ['email' => 'Reader@Example.com'])->assertRedirectContains('#newsletter')->assertSessionHas('newsletter');
        $this->post('/newsletter', ['email' => 'reader@example.com']);
        $this->assertSame(1, NewsletterSubscriber::count());

        $this->post('/newsletter', ['email' => 'not-an-email'])->assertSessionHasErrors('email', null, 'newsletter');
    }

    public function test_contact_form_saves_messages_and_ignores_bots(): void
    {
        $this->post('/contact', ['name' => 'Ravi', 'email' => 'ravi@example.com', 'message' => 'Do you deliver to Shimla?'])
            ->assertRedirect('/contact')->assertSessionHas('status');
        $this->assertSame(1, ContactMessage::count());

        $this->post('/contact', ['name' => 'Bot', 'email' => 'bot@example.com', 'message' => 'Buy cheap stuff now', 'website' => 'spam.example']);
        $this->assertSame(1, ContactMessage::count());

        $this->post('/contact', ['name' => '', 'email' => 'x', 'message' => 'short'])->assertSessionHasErrors(['name', 'email', 'message']);
    }

    public function test_colour_theme_setting_and_admin_preview(): void
    {
        $emeraldPrimary = '--color-primary: #0F8A6F;';

        // Default theme adds no overrides.
        $this->get('/')->assertDontSee($emeraldPrimary, false);

        // Guests can't preview; admins can, with a notice.
        $this->get('/?theme=emerald')->assertDontSee($emeraldPrimary, false);
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true, 'role' => 'admin'])->save();
        $this->actingAs($admin)->get('/?theme=emerald')->assertSee($emeraldPrimary, false)->assertSee('Previewing');

        // Saving the theme applies it for everyone; unknown themes are rejected.
        $this->put(route('admin.settings.update', 'store'), ['store__name' => 'Book Store', 'store__theme' => 'emerald'])->assertRedirect();
        $this->put(route('admin.settings.update', 'store'), ['store__name' => 'Book Store', 'store__theme' => 'neon'])->assertSessionHasErrors('store__theme');
        auth()->logout();
        $this->get('/')->assertSee($emeraldPrimary, false)->assertDontSee('Previewing');
    }

    public function test_admin_url_is_not_swallowed_by_page_routes(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
    }
}
