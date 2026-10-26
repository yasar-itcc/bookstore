<?php

namespace App\Models;

use App\Enums\BookFormat as Format;
use Database\Factories\BookFormatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['book_id', 'format', 'sku', 'mrp', 'price', 'stock', 'ebook_file', 'gst_rate', 'is_active'])]
class BookFormat extends Model
{
    /** @use HasFactory<BookFormatFactory> */
    use HasFactory;

    /**
     * Physical formats at or below this stock count show as "low stock".
     */
    public const LOW_STOCK_THRESHOLD = 5;

    protected function casts(): array
    {
        return [
            'format' => Format::class,
            'mrp' => 'decimal:2',
            'price' => 'decimal:2',
            'stock' => 'integer',
            'gst_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function scopePhysical(Builder $query): void
    {
        $query->where('format', '!=', Format::Ebook->value);
    }

    public function scopeLowStock(Builder $query): void
    {
        $query->physical()->where('stock', '>', 0)->where('stock', '<=', self::LOW_STOCK_THRESHOLD);
    }

    public function scopeOutOfStock(Builder $query): void
    {
        $query->physical()->where(fn (Builder $q) => $q->whereNull('stock')->orWhere('stock', 0));
    }

    public function discountPercent(): int
    {
        if ((float) $this->mrp <= 0 || (float) $this->price >= (float) $this->mrp) {
            return 0;
        }

        return (int) round((1 - $this->price / $this->mrp) * 100);
    }
}
