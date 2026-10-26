<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Support\Markdown;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'title', 'slug', 'category', 'excerpt', 'content', 'cover_image', 'meta_description', 'is_published', 'published_at'])]
class Post extends Model
{
    use HasSlug;

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'published_at' => 'datetime'];
    }

    protected function slugSource(): string
    {
        return 'title';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Published posts whose publish date has arrived, newest first.
     */
    public function scopeLive(Builder $query): void
    {
        $query->where('is_published', true)->where('published_at', '<=', now())->latest('published_at');
    }

    public function coverUrl(): ?string
    {
        return $this->cover_image ? Storage::disk('public')->url($this->cover_image) : null;
    }

    public function html(): string
    {
        return Markdown::toHtml($this->content);
    }

    public function summary(int $length = 140): string
    {
        return $this->excerpt ?: Str::limit(strip_tags($this->html()), $length);
    }

    public function readingMinutes(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags((string) $this->content)) / 200));
    }
}
