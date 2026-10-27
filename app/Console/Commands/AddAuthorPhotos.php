<?php

namespace App\Console\Commands;

use App\Models\Author;
use App\Support\CoverGenerator;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

#[Signature('authors:photos
    {--source=both : Where photos come from: openlibrary, generate or both}
    {--refresh : Replace photos this command added before (photos you uploaded are never touched)}
    {--limit=0 : Only process this many authors (0 = all)}')]
#[Description('Add photos to authors without one: real photos from Open Library, or a generated initials avatar')]
class AddAuthorPhotos extends Command
{
    /**
     * Photos added by this command live here, so they can be told apart from photos uploaded in the admin.
     */
    public const AUTO_DIRECTORY = 'authors/auto';

    public function handle(CoverGenerator $generator): int
    {
        $source = $this->option('source');
        if (! in_array($source, ['openlibrary', 'generate', 'both'], true)) {
            $this->error('--source must be openlibrary, generate or both.');

            return self::FAILURE;
        }

        $authors = Author::query()
            ->where(function ($query) {
                $query->whereNull('photo');
                if ($this->option('refresh')) {
                    $query->orWhere('photo', 'like', self::AUTO_DIRECTORY.'/%');
                }
            })
            ->orderBy('id')
            ->when((int) $this->option('limit'), fn ($query, $limit) => $query->limit($limit))
            ->get();

        if ($authors->isEmpty()) {
            $this->info('Every author already has a photo.');

            return self::SUCCESS;
        }

        $counts = ['openlibrary' => 0, 'generated' => 0, 'skipped' => 0];
        $disk = Storage::disk('public');

        $this->withProgressBar($authors, function (Author $author) use ($source, $generator, $disk, &$counts) {
            $path = null;

            if ($source !== 'generate' && ($image = $this->openLibraryPhoto($author->name))) {
                $path = self::AUTO_DIRECTORY."/{$author->slug}.jpg";
                $disk->put($path, $image);
                $counts['openlibrary']++;
            } elseif ($source !== 'openlibrary') {
                $path = self::AUTO_DIRECTORY."/{$author->slug}.svg";
                $disk->put($path, $generator->authorSvg($author->name));
                $counts['generated']++;
            } else {
                $counts['skipped']++;
            }

            if ($path) {
                if ($author->photo && $author->photo !== $path) {
                    $disk->delete($author->photo);
                }
                $author->forceFill(['photo' => $path])->saveQuietly();
            }
        });

        $this->newLine(2);
        $this->table(['Real photos (Open Library)', 'Generated avatars', 'No photo found'], [array_values($counts)]);

        return self::SUCCESS;
    }

    /**
     * Find the author on Open Library by name (the best-known match) and download their photo. Null when none.
     */
    private function openLibraryPhoto(string $name): ?string
    {
        try {
            $http = Http::withUserAgent('BookStore-admin/1.0 (author photo import)')->timeout(30)->retry(2, 1000, throw: false);

            $docs = $http->get('https://openlibrary.org/search/authors.json', [
                'q' => $name,
                'limit' => 5,
                'fields' => 'key,name,work_count',
            ])->json('docs', []);

            $wanted = $this->normalise($name);
            $match = collect($docs)
                ->filter(fn ($doc) => $this->normalise($doc['name'] ?? '') === $wanted)
                ->sortByDesc('work_count')
                ->first();

            if (! $match) {
                return null;
            }

            usleep(300_000); // Be gentle with Open Library's free service.

            $response = $http->get("https://covers.openlibrary.org/a/olid/{$match['key']}-L.jpg", ['default' => 'false']);

            // Tiny responses are placeholders, not real photos.
            return $response->successful() && str_starts_with((string) $response->header('Content-Type'), 'image/') && strlen($response->body()) > 3000
                ? $response->body()
                : null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * "J. R. R. Tolkien", "J.R.R. Tolkien" and "jrr tolkien" all compare equal.
     */
    private function normalise(string $name): string
    {
        return preg_replace('/[^a-z0-9]+/', '', Str::lower(Str::ascii($name)));
    }
}
