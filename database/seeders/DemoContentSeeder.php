<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Book;
use App\Models\Coupon;
use App\Models\Page;
use App\Models\Post;
use App\Models\PricingPlan;
use App\Models\Testimonial;
use App\Models\User;
use App\Support\CoverGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Demo storefront content: home slides and offer box, coupons, bestsellers, testimonials, pricing plans,
 * policy pages and blog posts. Safe to run more than once. Run after SampleCatalogSeeder.
 *
 * php artisan db:seed --class=DemoContentSeeder
 */
class DemoContentSeeder extends Seeder
{
    public function run(CoverGenerator $covers): void
    {
        $this->banners();
        $this->coupons();
        $this->bestsellers();
        $this->spreadAddedDates();
        $this->testimonials();
        $this->plans();
        $this->pages();
        $this->posts($covers);
        $this->call(AuthorBioSeeder::class);
    }

    private function banners(): void
    {
        $slides = [
            ['Welcome to Book Store', 'Your favourite books, all in one place', 'New releases, timeless classics, Hindi literature and exam guides, delivered across India with Cash on Delivery.', 'Shop now', '/books'],
            ['Bestsellers of the week', 'The books everyone is talking about', 'From Atomic Habits to The Silent Patient: see what readers across India are loving right now.', 'See bestsellers', '/books?show=bestsellers'],
            ['हिंदी साहित्य', 'Discover the classics of Hindi literature', 'Premchand, Dinkar, Bachchan and more. Timeless stories in the language they were written in.', 'Browse Hindi books', '/books?category=hindi-books'],
        ];

        foreach ($slides as $order => [$eyebrow, $title, $subtitle, $button, $link]) {
            Banner::updateOrCreate(['placement' => 'hero', 'title' => $title], [
                'eyebrow' => $eyebrow, 'subtitle' => $subtitle, 'button_text' => $button, 'button_link' => $link,
                'sort_order' => $order, 'is_active' => true,
            ]);
        }

        Banner::updateOrCreate(['placement' => 'promo', 'title' => '20% OFF'], [
            'eyebrow' => 'Big discount today',
            'subtitle' => 'On classic novels. Use code CLASSIC20 at checkout.',
            'button_text' => 'Shop classics',
            'button_link' => '/books?category=classic-novels',
            'is_active' => true,
        ]);
    }

    private function coupons(): void
    {
        $coupons = [
            ['WELCOME10', '10% off your first order', 'percent', 10, 299, 200, null, null, now()->addYear()],
            ['FLAT50', '₹50 off orders above ₹499', 'fixed', 50, 499, null, 500, null, now()->addMonths(3)],
            ['CLASSIC20', '20% off classic novels', 'percent', 20, 0, 300, null, null, now()->addMonth()],
            ['DIWALI25', 'Festival sale (ended)', 'percent', 25, 999, 500, null, now()->subMonths(2), now()->subMonth()],
        ];

        foreach ($coupons as [$code, $description, $type, $value, $min, $max, $limit, $starts, $expires]) {
            Coupon::updateOrCreate(['code' => $code], [
                'description' => $description, 'type' => $type, 'value' => $value, 'min_order_amount' => $min,
                'max_discount' => $max, 'usage_limit' => $limit, 'starts_at' => $starts, 'expires_at' => $expires, 'is_active' => true,
            ]);
        }
    }

    private function bestsellers(): void
    {
        Book::whereIn('title', [
            'Atomic Habits', 'The Psychology of Money', 'The Silent Patient', 'It Ends with Us', 'The Alchemist', 'Ikigai',
            'Harry Potter and the Philosopher\'s Stone', 'Wings of Fire', 'The Immortals of Meluha', 'Rich Dad Poor Dad',
            'Indian Polity', 'The Hobbit',
        ])->update(['is_bestseller' => true]);
    }

    /**
     * Sample books were all added in one go; give them varied "added" dates over the last two months
     * so New Arrivals shows a natural mix of categories.
     */
    private function spreadAddedDates(): void
    {
        Book::where('created_at', '>', now()->subDay())->get()->each(function (Book $book) {
            $book->timestamps = false;
            $book->forceFill(['created_at' => now()->subDays(crc32($book->title) % 60)->subMinutes(crc32($book->slug) % 600)])->save();
        });
    }

    private function testimonials(): void
    {
        $testimonials = [
            ['Priya Nair', 'Teacher, Kochi', 'I ordered books for my whole class and every single one arrived in perfect condition. Paying on delivery made it so easy.', 5],
            ['Arjun Mehta', 'UPSC aspirant, Delhi', 'Found all my standard UPSC books in one place at better prices than my local market. Delivery took just three days.', 5],
            ['Fatima Sheikh', 'Book club host, Hyderabad', 'Our book club orders here every month. Great choice of Hindi and English titles, and the team answers questions quickly.', 4],
        ];

        foreach ($testimonials as $order => [$name, $role, $quote, $rating]) {
            Testimonial::updateOrCreate(['name' => $name], ['role' => $role, 'quote' => $quote, 'rating' => $rating, 'sort_order' => $order, 'is_active' => true]);
        }
    }

    private function plans(): void
    {
        $plans = [
            ['Reader', 0, 'month', 'For the occasional reader', ['Free delivery above ₹499', 'Cash on Delivery', 'Monthly newsletter with picks', 'Member-only flash sales'], 'Join free', false],
            ['Bookworm', 149, 'month', 'For readers who buy a book or two every month', ['Free delivery on every order', '10% off all books', 'Early access to new releases', 'One free eBook every month', 'Priority customer support'], 'Choose Bookworm', true],
            ['Library', 1499, 'year', 'Best value for families, schools and book clubs', ['Everything in Bookworm', '15% off all books', 'Bulk order pricing', 'Dedicated account manager', 'Two months free compared to monthly'], 'Choose Library', false],
        ];

        foreach ($plans as $order => [$name, $price, $period, $description, $features, $button, $highlight]) {
            PricingPlan::updateOrCreate(['name' => $name], [
                'price' => $price, 'period' => $period, 'description' => $description, 'features' => $features,
                'button_text' => $button, 'button_link' => '/contact', 'is_highlighted' => $highlight, 'sort_order' => $order, 'is_active' => true,
            ]);
        }
    }

    private function pages(): void
    {
        foreach (require __DIR__.'/data/pages.php' as $page) {
            Page::updateOrCreate(['title' => $page['title']], [
                'content' => $page['content'], 'meta_description' => $page['meta'], 'show_in_footer' => true, 'is_published' => true,
            ]);
        }
    }

    private function posts(CoverGenerator $covers): void
    {
        $author = User::where('is_admin', true)->orderBy('id')->first();
        $posts = require __DIR__.'/data/posts.php';

        foreach ($posts as $index => $data) {
            // Newest first in the file; spread publish dates over the last few months.
            $publishedAt = now()->subDays($index * 6 + 1)->setTime(10, 0);
            $coverPath = 'posts/auto/'.Str::slug($data['title']).'.svg';
            Storage::disk('public')->put($coverPath, $covers->postSvg($data['title'], $data['category']));

            Post::updateOrCreate(['title' => $data['title']], [
                'user_id' => $author?->id,
                'category' => $data['category'],
                'excerpt' => $data['excerpt'],
                'content' => trim($data['content']),
                'cover_image' => $coverPath,
                'meta_description' => $data['excerpt'],
                'is_published' => true,
                'published_at' => $publishedAt,
            ]);
        }
    }
}
