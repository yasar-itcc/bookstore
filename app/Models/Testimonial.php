<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'role', 'quote', 'rating', 'photo', 'sort_order', 'is_active'])]
class Testimonial extends Model
{
    protected function casts(): array
    {
        return ['rating' => 'integer', 'sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    public function photoUrl(): ?string
    {
        return $this->photo ? Storage::disk('public')->url($this->photo) : null;
    }
}
