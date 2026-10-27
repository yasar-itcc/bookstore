<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookFormat;
use Database\Seeders\AuthorBioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthorPagesTest extends TestCase
{
    use RefreshDatabase;

    private function authorWithBook(array $attributes = [], array $book = []): Author
    {
        $author = Author::factory()->create($attributes);
        $book = Book::factory()->create($book);
        BookFormat::factory()->for($book)->create();
        $book->authors()->attach($author);

        return $author;
    }

    public function test_authors_list_shows_authors_with_books_and_filters_by_letter_and_search(): void
    {
        $this->authorWithBook(['name' => 'Ruskin Bond']);
        $this->authorWithBook(['name' => 'Amrita Pritam']);
        Author::factory()->create(['name' => 'Nobody Withoutbooks']);
        $this->authorWithBook(['name' => 'Hidden Author', 'is_active' => false]);

        $this->get('/authors')->assertOk()
            ->assertSee('Ruskin Bond')->assertSee('Amrita Pritam')
            ->assertDontSee('Nobody Withoutbooks')->assertDontSee('Hidden Author');

        $this->get('/authors?letter=R')->assertOk()->assertSee('Ruskin Bond')->assertDontSee('Amrita Pritam');
        $this->get('/authors?search=amrita')->assertOk()->assertSee('Amrita Pritam')->assertDontSee('Ruskin Bond');
    }

    public function test_author_page_shows_bio_photo_and_books(): void
    {
        $author = $this->authorWithBook(
            ['name' => 'Ruskin Bond', 'bio' => "Ruskin Bond writes about the hills.\n\nHe lives in Mussoorie.", 'photo' => 'authors/ruskin.jpg'],
            ['title' => 'The Blue Umbrella'],
        );

        $this->get('/authors/'.$author->slug)->assertOk()
            ->assertSee('Ruskin Bond writes about the hills.')
            ->assertSee('He lives in Mussoorie.')
            ->assertSee('The Blue Umbrella')
            ->assertSee(Storage::disk('public')->url('authors/ruskin.jpg'));

        $author->update(['is_active' => false]);
        $this->get('/authors/'.$author->slug)->assertNotFound();
    }

    public function test_book_page_links_to_the_author_and_shows_their_bio(): void
    {
        $author = $this->authorWithBook(['name' => 'Ruskin Bond', 'bio' => 'Ruskin Bond writes about the hills.'], ['title' => 'The Blue Umbrella']);
        $book = $author->books->first();

        $this->get('/books/'.$book->slug)->assertOk()
            ->assertSee(route('authors.show', $author))
            ->assertSee('About the author')
            ->assertSee('Ruskin Bond writes about the hills.');
    }

    public function test_bio_seeder_fills_empty_bios_only(): void
    {
        $kalam = Author::factory()->create(['name' => 'A. P. J. Abdul Kalam', 'bio' => null]);
        $austen = Author::factory()->create(['name' => 'Jane Austen', 'bio' => 'My own bio.']);

        $this->seed(AuthorBioSeeder::class);

        $this->assertStringContainsString('Missile Man', $kalam->fresh()->bio);
        $this->assertSame('My own bio.', $austen->fresh()->bio);
    }

    public function test_photo_command_uses_open_library_or_generates_an_avatar(): void
    {
        Storage::fake('public');
        $orwell = Author::factory()->create(['name' => 'George Orwell', 'photo' => null]);
        $unknown = Author::factory()->create(['name' => 'Someone Obscure', 'photo' => null]);
        $uploaded = Author::factory()->create(['name' => 'Has Own Photo', 'photo' => 'authors/mine.jpg']);

        Http::fake([
            'openlibrary.org/search/authors.json*' => fn ($request) => Http::response(['docs' => str_contains($request->url(), 'Orwell')
                ? [['key' => 'OL1A', 'name' => 'George Orwell', 'work_count' => 900], ['key' => 'OL2A', 'name' => 'George Orwell', 'work_count' => 3]]
                : []]),
            'covers.openlibrary.org/a/olid/OL1A-L.jpg*' => Http::response(str_repeat('x', 4000), 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $this->artisan('authors:photos --refresh')->assertSuccessful();

        $this->assertSame('authors/auto/george-orwell.jpg', $orwell->fresh()->photo);
        $this->assertStringEndsWith('.svg', $unknown->fresh()->photo);
        $this->assertStringContainsString('>SO<', Storage::disk('public')->get($unknown->fresh()->photo));
        $this->assertSame('authors/mine.jpg', $uploaded->fresh()->photo);
    }
}
