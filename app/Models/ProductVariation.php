<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductVariation extends Model
{
    use HasFactory;

    public const STATUS_PUBLISH = 'publish';
    public const STATUS_PRIVATE = 'private';

    public const STOCK_IN_STOCK = 'instock';
    public const STOCK_OUT_OF_STOCK = 'outofstock';
    public const STOCK_ON_BACKORDER = 'onbackorder';

    protected $fillable = [
        'product_id',
        'sku',
        'combo_key',
        'status',
        'description',
        'regular_price',
        'sale_price',
        'manage_stock',
        'stock_quantity',
        'stock_status',
        'image',
        'weight_value',
        'menu_order',
        'size',
        'weight',
        'color',
        'price',
        'stock',
    ];

    protected $casts = [
        'regular_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'price' => 'decimal:2',
        'weight_value' => 'decimal:3',
        'stock_quantity' => 'integer',
        'stock' => 'integer',
        'manage_stock' => 'boolean',
        'menu_order' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues()
    {
        return $this->hasMany(VariationAttributeValue::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isEnabled(): bool
    {
        return $this->status !== self::STATUS_PRIVATE;
    }

    public function isOnSale(): bool
    {
        return $this->sale_price !== null && (float) $this->sale_price < (float) ($this->regular_price ?? 0);
    }

    /**
     * Active price: the sale price when it is lower than the regular price,
     * otherwise the regular price.
     *
     * A variable parent has no price of its own, so a variation without a
     * price of its own has no price rather than inheriting one.
     */
    public function getActivePriceAttribute()
    {
        if ($this->isOnSale()) {
            return (float) $this->sale_price;
        }

        if ($this->regular_price !== null && $this->regular_price !== '') {
            return (float) $this->regular_price;
        }

        return null;
    }

    /**
     * Stock quantity with inheritance from the parent when this variation does
     * not manage its own stock. The raw column is always `$stock_quantity`.
     */
    public function effectiveStockQuantity(): ?int
    {
        if ($this->manage_stock) {
            return $this->stock_quantity;
        }

        return $this->product?->stock_quantity;
    }

    /**
     * Whether a quantity limit applies to this variation at all.
     *
     * With "Manage stock" off on both the variation and its parent, nothing is
     * counted, so a quantity of 0 must not be read as "none left" by the cart.
     */
    public function isStockTracked(): bool
    {
        return (bool) ($this->manage_stock || $this->product?->manage_stock);
    }

    /**
     * Stock status with inheritance from the parent. The raw column is
     * always `$stock_status`.
     */
    public function effectiveStockStatus(): string
    {
        if ($this->manage_stock) {
            $quantity = $this->stock_quantity;

            if ($quantity === null || $quantity <= 0) {
                return self::STOCK_OUT_OF_STOCK;
            }

            return $this->stock_status ?: self::STOCK_IN_STOCK;
        }

        // Stock is tracked on the parent, so the variation's own status is
        // ignored entirely.
        $parent = $this->product;

        if ($parent === null) {
            return $this->stock_status ?: self::STOCK_IN_STOCK;
        }

        if ($parent->manage_stock) {
            $parentQuantity = $parent->stock_quantity;

            return ($parentQuantity === null || $parentQuantity <= 0)
                ? self::STOCK_OUT_OF_STOCK
                : self::STOCK_IN_STOCK;
        }

        return $parent->stock_status ?: self::STOCK_IN_STOCK;
    }

    public function isInStock(): bool
    {
        return $this->effectiveStockStatus() !== self::STOCK_OUT_OF_STOCK;
    }

    /**
     * Why this variation cannot be bought, or null when it can.
     *
     * The storefront sends this to the product page so a shopper is never
     * offered an option that the cart is going to reject.
     */
    public function unavailabilityReason(): ?string
    {
        if (! $this->isEnabled()) {
            return 'This option is not available.';
        }

        if (! $this->isInStock()) {
            return 'This option is out of stock.';
        }

        if ($this->active_price === null) {
            return 'This option has no price yet.';
        }

        return null;
    }

    public function isPurchasable(): bool
    {
        return $this->unavailabilityReason() === null;
    }

    /**
     * Take `quantity` units out of stock, honouring where the stock is
     * actually tracked and keeping the legacy `stock` mirror in step.
     */
    public function reduceStock(int $quantity): void
    {
        $quantity = max(0, $quantity);

        if ($this->manage_stock) {
            $remaining = max(0, (int) $this->stock_quantity - $quantity);

            $this->update([
                'stock_quantity' => $remaining,
                'stock' => $remaining,
                'stock_status' => $remaining > 0 ? self::STOCK_IN_STOCK : self::STOCK_OUT_OF_STOCK,
            ]);

            return;
        }

        // Stock lives on the parent, so nothing to change here.
    }

    public function getWeightAttribute()
    {
        if ($this->weight_value !== null) {
            return (float) $this->weight_value;
        }

        return $this->product?->weight_value;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    /**
     * The selected attribute values keyed by product attribute id.
     *
     * @return \Illuminate\Support\Collection<int, VariationAttributeValue>
     */
    public function valuesByProductAttribute()
    {
        return $this->attributeValues->keyBy('product_attribute_id');
    }

    /**
     * A human readable label such as "Pink / M".
     */
    public function getAttributeLabelAttribute(): string
    {
        $parts = [];

        foreach ($this->attributeValues->sortBy(fn ($v) => $v->productAttribute?->position ?? 0) as $value) {
            $name = $value->displayValue();

            if ($name !== null && $name !== '') {
                $parts[] = $name;
            }
        }

        return implode(' / ', $parts);
    }

    /**
     * The image shown for this variation, falling back to the parent image.
     */
    public function getDisplayImageAttribute(): ?string
    {
        if ($this->image) {
            return $this->image;
        }

        $featured = $this->product?->images?->where('is_featured', true)->first();

        return $featured?->image ?? $this->product?->images?->first()?->image;
    }

    public function scopeEnabled($query)
    {
        return $query->where('status', self::STATUS_PUBLISH);
    }

    protected static function booted()
    {
        static::deleting(function (self $variation) {
            if ($variation->image) {
                Storage::disk('public')->delete($variation->image);
            }
        });
    }
}
