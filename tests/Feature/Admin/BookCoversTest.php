<?php

namespace Tests\Feature\Admin;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookCoversTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_it_uses_the_open_library_cover_when_the_title_matches(): void
    {
        $book = Book::factory()->hasAttached(Author::factory(['name' => 'James Clear']))->create(['title' => 'Atomic Habits']);

        Http::fake([
            'openlibrary.org/search.json*' => Http::response(['docs' => [
                ['title' => 'Atomic Habits Workbook', 'cover_i' => 1],
                ['title' => 'Atomic Habits', 'cover_i' => 2],
            ]]),
            'covers.openlibrary.org/b/id/2-L.jpg*' => Http::response(str_repeat('x', 6000), 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $this->artisan('books:covers')->assertSuccessful();

        $this->assertSame('covers/auto/atomic-habits.jpg', $book->fresh()->cover_image);
        Storage::disk('public')->assertExists('covers/auto/atomic-habits.jpg');
    }

    public function test_it_generates_a_cover_when_open_library_has_none(): void
    {
        $book = Book::factory()->hasAttached(Author::factory(['name' => 'Munshi Premchand']))->create(['title' => 'Nirmala & <Friends>']);

        Http::fake(['openlibrary.org/*' => Http::response(['docs' => []])]);

        $this->artisan('books:covers')->assertSuccessful();

        $path = $book->fresh()->cover_image;
        $this->assertStringEndsWith('.svg', $path);
        $svg = Storage::disk('public')->get($path);
        $this->assertStringContainsString('Nirmala &amp;', $svg);
        $this->assertStringContainsString('&lt;Friends&gt;', $svg);
        $this->assertStringContainsString('Munshi Premchand', $svg);
        $this->assertNotFalse(simplexml_load_string($svg), 'Generated cover is valid XML');
    }

    public function test_uploaded_covers_are_never_replaced(): void
    {
        $book = Book::factory()->create(['cover_image' => 'covers/my-photo.jpg']);
        Http::fake();

        $this->artisan('books:covers --refresh')->assertSuccessful();

        $this->assertSame('covers/my-photo.jpg', $book->fresh()->cover_image);
        Http::assertNothingSent();
    }
}
