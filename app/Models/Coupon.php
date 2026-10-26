<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'description', 'type', 'value', 'min_order_amount', 'max_discount', 'usage_limit', 'starts_at', 'expires_at', 'is_active'])]
class Coupon extends Model
{
    public const TYPES = ['percent' => 'Percentage (%)', 'fixed' => 'Fixed amount (₹)'];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected function setCodeAttribute(string $value): void
    {
        $this->attributes['code'] = strtoupper(trim($value));
    }

    /**
     * Active, Scheduled, Expired, Used up or Inactive.
     */
    public function status(): string
    {
        return match (true) {
            ! $this->is_active => 'Inactive',
            $this->expires_at?->isPast() => 'Expired',
            $this->starts_at?->isFuture() => 'Scheduled',
            $this->usage_limit !== null && $this->used_count >= $this->usage_limit => 'Used up',
            default => 'Active',
        };
    }

    public function isUsable(): bool
    {
        return $this->status() === 'Active';
    }

    /**
     * Discount in rupees for an order subtotal (0 when the coupon doesn't apply).
     */
    public function discountFor(float $subtotal): float
    {
        if (! $this->isUsable() || $subtotal < (float) $this->min_order_amount) {
            return 0.0;
        }

        $discount = $this->type === 'percent' ? $subtotal * (float) $this->value / 100 : (float) $this->value;

        if ($this->max_discount !== null) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return round(min($discount, $subtotal), 2);
    }

    public function label(): string
    {
        return $this->type === 'percent' ? rtrim(rtrim((string) $this->value, '0'), '.').'% off' : money($this->value).' off';
    }
}
