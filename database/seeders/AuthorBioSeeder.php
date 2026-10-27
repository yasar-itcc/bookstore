<?php

namespace Database\Seeders;

use App\Models\Author;
use Illuminate\Database\Seeder;

/**
 * Fills in biographies for the sample catalog's authors. Bios you've already written in the admin are kept.
 *
 * php artisan db:seed --class=AuthorBioSeeder
 */
class AuthorBioSeeder extends Seeder
{
    public function run(): void
    {
        foreach (require __DIR__.'/data/author-bios.php' as $name => $bio) {
            Author::where('name', $name)
                ->where(fn ($query) => $query->whereNull('bio')->orWhere('bio', ''))
                ->update(['bio' => $bio]);
        }
    }
}
