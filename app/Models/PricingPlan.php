<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'price', 'period', 'description', 'features', 'button_text', 'button_link', 'is_highlighted', 'sort_order', 'is_active'])]
class PricingPlan extends Model
{
    public const PERIODS = ['month' => 'per month', 'year' => 'per year', 'once' => 'one time'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'features' => 'array',
            'is_highlighted' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
