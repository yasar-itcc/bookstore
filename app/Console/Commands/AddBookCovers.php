<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Support\CoverGenerator;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

#[Signature('books:covers
    {--source=both : Where covers come from: openlibrary, generate or both}
    {--refresh : Replace covers this command added before (covers you uploaded are never touched)}
    {--limit=0 : Only process this many books (0 = all)}')]
#[Description('Add cover images to books without one: real covers from Open Library, or a generated branded cover')]
class AddBookCovers extends Command
{
    /**
     * Covers added by this command live here, so they can be told apart from covers uploaded in the admin.
     */
    public const AUTO_DIRECTORY = 'covers/auto';

    public function handle(CoverGenerator $generator): int
    {
        $source = $this->option('source');
        if (! in_array($source, ['openlibrary', 'generate', 'both'], true)) {
            $this->error('--source must be openlibrary, generate or both.');

            return self::FAILURE;
        }

        $books = Book::with(['authors', 'category'])
            ->where(function ($query) {
                $query->whereNull('cover_image');
                if ($this->option('refresh')) {
                    $query->orWhere('cover_image', 'like', self::AUTO_DIRECTORY.'/%');
                }
            })
            ->orderBy('id')
            ->when((int) $this->option('limit'), fn ($query, $limit) => $query->limit($limit))
            ->get();

        if ($books->isEmpty()) {
            $this->info('Every book already has a cover.');

            return self::SUCCESS;
        }

        $counts = ['openlibrary' => 0, 'generated' => 0, 'skipped' => 0];
        $disk = Storage::disk('public');

        $this->withProgressBar($books, function (Book $book) use ($source, $generator, $disk, &$counts) {
            $path = null;

            if ($source !== 'generate' && ($image = $this->openLibraryCover($book))) {
                $path = self::AUTO_DIRECTORY."/{$book->slug}.jpg";
                $disk->put($path, $image);
                $counts['openlibrary']++;
            } elseif ($source !== 'openlibrary') {
                $path = self::AUTO_DIRECTORY."/{$book->slug}.svg";
                $disk->put($path, $generator->svg($book));
                $counts['generated']++;
            } else {
                $counts['skipped']++;
            }

            if ($path) {
                if ($book->cover_image && $book->cover_image !== $path) {
                    $disk->delete($book->cover_image);
                }
                $book->forceFill(['cover_image' => $path])->saveQuietly();
            }
        });

        $this->newLine(2);
        $this->table(['Real covers (Open Library)', 'Generated covers', 'No cover found'], [array_values($counts)]);

        return self::SUCCESS;
    }

    /**
     * Find the book on Open Library by title and first author, and download its large cover. Null when there's no good match.
     */
    private function openLibraryCover(Book $book): ?string
    {
        try {
            $http = Http::withUserAgent('BookStore-admin/1.0 (cover import)')->timeout(30)->retry(2, 1000, throw: false);

            $docs = $http->get('https://openlibrary.org/search.json', [
                'title' => $book->title,
                'author' => $book->authors->first()?->name,
                'limit' => 5,
                'fields' => 'title,cover_i',
            ])->json('docs', []);

            $wanted = $this->normalise($book->title);
            $match = collect($docs)->first(fn ($doc) => ! empty($doc['cover_i'])
                && (($found = $this->normalise($doc['title'] ?? '')) === $wanted || Str::startsWith($found, $wanted.' ')));

            if (! $match) {
                return null;
            }

            // Be gentle with Open Library's free service.
            usleep(300_000);

            $response = $http->get("https://covers.openlibrary.org/b/id/{$match['cover_i']}-L.jpg", ['default' => 'false']);

            // Tiny responses are placeholders, not real covers.
            return $response->successful() && str_starts_with((string) $response->header('Content-Type'), 'image/') && strlen($response->body()) > 5000
                ? $response->body()
                : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    private function normalise(string $title): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', ' ', Str::lower(Str::ascii($title))));
    }
}
