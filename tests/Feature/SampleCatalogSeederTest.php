<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookFormat;
use App\Models\Category;
use Database\Seeders\SampleCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SampleCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_ten_valid_books_per_category_and_can_run_twice(): void
    {
        $this->seed(SampleCatalogSeeder::class);
        $this->seed(SampleCatalogSeeder::class);

        $this->assertSame(130, Book::count());
        Category::has('books')->withCount('books')->get()
            ->each(fn ($category) => $this->assertSame(10, $category->books_count, $category->name));

        $this->assertSame(0, BookFormat::whereColumn('price', '>', 'mrp')->count());
        $this->assertSame(0, Book::doesntHave('formats')->count());
        $this->assertSame(0, Book::doesntHave('authors')->count());
    }
}
