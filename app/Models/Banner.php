<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['placement', 'eyebrow', 'title', 'subtitle', 'button_text', 'button_link', 'image', 'sort_order', 'is_active'])]
class Banner extends Model
{
    public const PLACEMENTS = [
        'hero' => 'Home page slider',
        'promo' => 'Home page offer box (beside the category columns)',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function scopeLive(Builder $query, string $placement): void
    {
        $query->where('placement', $placement)->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function imageUrl(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }
}
