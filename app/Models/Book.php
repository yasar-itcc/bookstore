<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'title', 'slug', 'subtitle', 'isbn', 'description', 'category_id', 'publisher_id', 'language_id',
    'pages', 'published_year', 'cover_image', 'is_featured', 'is_bestseller', 'is_active',
])]
class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory, HasSlug;

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_bestseller' => 'boolean',
            'is_active' => 'boolean',
            'pages' => 'integer',
            'published_year' => 'integer',
        ];
    }

    protected function slugSource(): string
    {
        return 'title';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class);
    }

    public function formats(): HasMany
    {
        return $this->hasMany(BookFormat::class);
    }

    public function coverUrl(): ?string
    {
        return $this->cover_image ? Storage::disk('public')->url($this->cover_image) : null;
    }

    /**
     * Lowest selling price across the active formats, for list views.
     */
    public function startingPrice(): ?float
    {
        $price = $this->formats->where('is_active', true)->min('price');

        return $price === null ? null : (float) $price;
    }
}
