<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    const TYPE_PERCENT = 'percent';
    const TYPE_FIXED = 'fixed';

    const TYPES = [
        self::TYPE_PERCENT => 'Percentage (%)',
        self::TYPE_FIXED => 'Fixed Amount (৳)',
    ];

    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'per_user_limit',
        'is_active',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'value' => 'float',
        'min_order_amount' => 'float',
        'max_discount_amount' => 'float',
        'usage_limit' => 'integer',
        'per_user_limit' => 'integer',
        'used_count' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getValueLabelAttribute(): string
    {
        if ($this->type !== self::TYPE_PERCENT) {
            return '৳' . number_format((float) $this->value, 0);
        }

        // 10.00 -> "10%", 12.50 -> "12.5%"
        $value = rtrim(rtrim(number_format((float) $this->value, 2, '.', ''), '0'), '.');

        return $value . '%';
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) {
            return 'Disabled';
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'Expired';
        }
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'Scheduled';
        }
        if ($this->isExhausted()) {
            return 'Limit Reached';
        }
        return 'Active';
    }

    public function isExpired(): bool
    {
        return (bool) $this->expires_at && $this->expires_at->isPast();
    }

    public function isScheduled(): bool
    {
        return (bool) $this->starts_at && $this->starts_at->isFuture();
    }

    public function isExhausted(): bool
    {
        return (bool) $this->usage_limit && $this->used_count >= $this->usage_limit;
    }

    /**
     * Check whether this coupon can be applied to the given subtotal for the
     * given user. Returns a human readable reason when it cannot.
     */
    public function rejectionReason(float $subtotal, ?int $userId = null): ?string
    {
        if (!$this->is_active) {
            return 'This coupon is no longer active.';
        }
        if ($this->isExpired()) {
            return 'This coupon has expired.';
        }
        if ($this->isScheduled()) {
            return 'This coupon is not active yet.';
        }
        if ($this->isExhausted()) {
            return 'This coupon has reached its usage limit.';
        }
        if ($this->min_order_amount && $subtotal < $this->min_order_amount) {
            return 'Minimum order of ৳' . number_format($this->min_order_amount, 0) . ' required for this coupon.';
        }
        if ($userId && $this->per_user_limit) {
            $used = Order::where('coupon_id', $this->id)->where('user_id', $userId)->count();
            if ($used >= $this->per_user_limit) {
                return 'You have already used this coupon the maximum number of times.';
            }
        }

        return null;
    }

    public function isUsableFor(float $subtotal, ?int $userId = null): bool
    {
        return $this->rejectionReason($subtotal, $userId) === null;
    }

    /**
     * Discount amount for the given subtotal. Never returns more than the subtotal.
     */
    public function discountFor(float $subtotal): float
    {
        $discount = $this->type === self::TYPE_PERCENT
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        if ($this->type === self::TYPE_PERCENT && $this->max_discount_amount) {
            $discount = min($discount, (float) $this->max_discount_amount);
        }

        return round(max(0, min($discount, $subtotal)), 2);
    }
}
