<?php

namespace Tests\Feature\Admin;

use App\Enums\BookFormat as Format;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookFormat;
use App\Models\Category;
use App\Models\Language;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $this->actingAs($admin);
    }

    public function test_categories_can_be_created_updated_and_deleted(): void
    {
        $this->post(route('admin.categories.store'), ['name' => 'Islamic Books', 'is_active' => 1])
            ->assertRedirect(route('admin.categories.index'));

        $category = Category::firstWhere('name', 'Islamic Books');
        $this->assertSame('islamic-books', $category->slug);

        $this->put(route('admin.categories.update', $category), ['name' => 'Islamic', 'slug' => 'islamic', 'is_active' => 0])
            ->assertRedirect();
        $this->assertFalse($category->fresh()->is_active);

        $this->delete(route('admin.categories.destroy', $category))->assertRedirect();
        $this->assertModelMissing($category);
    }

    public function test_a_category_with_books_cannot_be_deleted(): void
    {
        $book = Book::factory()->create();

        $this->delete(route('admin.categories.destroy', $book->category))->assertSessionHas('warning');
        $this->assertModelExists($book->category);
    }

    public function test_authors_publishers_and_languages_can_be_managed(): void
    {
        $this->post(route('admin.authors.store'), ['name' => 'Ruskin Bond', 'is_active' => 1])->assertRedirect();
        $this->post(route('admin.publishers.store'), ['name' => 'Rupa', 'website' => 'https://rupapublications.co.in', 'is_active' => 1])->assertRedirect();
        $this->post(route('admin.languages.store'), ['name' => 'Kannada', 'code' => 'kn', 'is_active' => 1])->assertRedirect();

        $this->assertDatabaseHas('authors', ['slug' => 'ruskin-bond']);
        $this->assertDatabaseHas('publishers', ['slug' => 'rupa']);
        $this->assertDatabaseHas('languages', ['code' => 'kn']);

        // Language codes must be unique (English is seeded by the migration).
        $this->post(route('admin.languages.store'), ['name' => 'English 2', 'code' => 'en'])->assertSessionHasErrors('code');
    }

    public function test_a_book_is_created_with_authors_cover_and_formats(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $category = Category::factory()->create();
        $authors = Author::factory()->count(2)->create();
        $publisher = Publisher::factory()->create();

        $response = $this->post(route('admin.books.store'), [
            'title' => 'Wings of Fire',
            'isbn' => '9788173711466',
            'category_id' => $category->id,
            'publisher_id' => $publisher->id,
            'language_id' => Language::firstWhere('code', 'en')->id,
            'author_ids' => $authors->pluck('id')->all(),
            'is_active' => 1,
            'is_featured' => 1,
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 300, 400),
            'formats' => [
                'paperback' => ['enabled' => 1, 'mrp' => 350, 'price' => 299, 'stock' => 12, 'gst_rate' => 0, 'is_active' => 1],
                'hardcover' => ['enabled' => 0],
                'ebook' => ['enabled' => 1, 'mrp' => 199, 'price' => 149, 'gst_rate' => 18, 'is_active' => 1,
                    'ebook_file' => UploadedFile::fake()->create('wings.pdf', 500, 'application/pdf')],
            ],
        ]);

        $book = Book::firstWhere('title', 'Wings of Fire');
        $response->assertRedirect(route('admin.books.edit', $book));

        $this->assertSame('wings-of-fire', $book->slug);
        $this->assertCount(2, $book->authors);
        Storage::disk('public')->assertExists($book->cover_image);

        $this->assertCount(2, $book->formats);
        $paperback = $book->formats->firstWhere('format', Format::Paperback);
        $this->assertSame(12, $paperback->stock);
        $this->assertSame(15, $paperback->discountPercent());

        $ebook = $book->formats->firstWhere('format', Format::Ebook);
        $this->assertNull($ebook->stock);
        $this->assertSame('18.00', $ebook->gst_rate);
        Storage::disk('local')->assertExists($ebook->ebook_file);
        Storage::disk('public')->assertMissing($ebook->ebook_file);

        $this->get(route('admin.books.index'))->assertOk()->assertSee('Wings of Fire');
        $this->get(route('admin.books.edit', $book))->assertOk()->assertSee('File uploaded');
    }

    public function test_turning_a_format_off_removes_it(): void
    {
        $book = Book::factory()->create();
        BookFormat::factory()->for($book)->create();

        $this->put(route('admin.books.update', $book), [
            'title' => $book->title,
            'category_id' => $book->category_id,
            'formats' => [
                'paperback' => ['enabled' => 0],
                'hardcover' => ['enabled' => 1, 'mrp' => 600, 'price' => 550, 'stock' => 3, 'gst_rate' => 0],
                'ebook' => ['enabled' => 0],
            ],
        ])->assertRedirect(route('admin.books.edit', $book));

        $this->assertSame([Format::Hardcover], $book->fresh()->formats->pluck('format')->all());
    }

    public function test_book_validation(): void
    {
        $category = Category::factory()->create();

        // No format switched on.
        $this->post(route('admin.books.store'), ['title' => 'No formats', 'category_id' => $category->id, 'formats' => [
            'paperback' => ['enabled' => 0], 'hardcover' => ['enabled' => 0], 'ebook' => ['enabled' => 0],
        ]])->assertSessionHasErrors('formats');

        // Price above MRP, and missing stock for a printed format.
        $this->post(route('admin.books.store'), ['title' => 'Bad price', 'category_id' => $category->id, 'formats' => [
            'paperback' => ['enabled' => 1, 'mrp' => 100, 'price' => 150, 'gst_rate' => 0],
        ]])->assertSessionHasErrors(['formats.paperback.price', 'formats.paperback.stock']);

        $this->assertDatabaseCount('books', 0);
    }

    public function test_deleting_a_book_removes_its_files(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $book = Book::factory()->create(['cover_image' => UploadedFile::fake()->image('c.jpg')->store('covers', 'public')]);
        $ebook = BookFormat::factory()->for($book)->ebook()->create([
            'ebook_file' => UploadedFile::fake()->create('b.pdf', 10)->store('ebooks', 'local'),
        ]);

        $this->delete(route('admin.books.destroy', $book))->assertRedirect(route('admin.books.index'));

        $this->assertModelMissing($book);
        $this->assertModelMissing($ebook);
        Storage::disk('public')->assertMissing($book->cover_image);
        Storage::disk('local')->assertMissing($ebook->ebook_file);
    }

    public function test_dashboard_shows_catalog_numbers_and_low_stock(): void
    {
        $book = Book::factory()->create(['title' => 'Almost Gone']);
        BookFormat::factory()->for($book)->stock(2)->create(['price' => 100]);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Almost Gone')
            ->assertSee('2 left')
            ->assertSee('₹200');
    }

    public function test_books_can_be_searched_and_filtered(): void
    {
        $match = Book::factory()->create(['title' => 'The Alchemist']);
        Book::factory()->create(['title' => 'Atomic Habits']);

        $this->get(route('admin.books.index', ['search' => 'Alchemist']))
            ->assertSee('The Alchemist')
            ->assertDontSee('Atomic Habits');

        $this->get(route('admin.books.index', ['category' => $match->category_id]))
            ->assertSee('The Alchemist')
            ->assertDontSee('Atomic Habits');
    }
}
