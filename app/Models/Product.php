<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'product_type',
        'regular_price',
        'sale_price',
        'cost_price',
        'min_price',
        'max_price',
        'stock_quantity',
        'manage_stock',
        'stock_status',
        'status',
        'is_active',
        'is_featured',
        'size_chart_id',
        'sort_order',
        'seo_title',
        'meta_description',
    ];

    protected $casts = [
        'regular_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'manage_stock' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Internal costing. Never rendered on the storefront and never serialised,
     * so a stray toArray()/toJson() cannot leak it. Admin views read the
     * attribute directly.
     */
    protected $hidden = [
        'cost_price',
    ];

    public function vendor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('order')->orderBy('id');
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class)->orderBy('menu_order')->orderBy('id');
    }

    /**
     * At most one size chart per product. Null when the admin did not pick one,
     * in which case the storefront hides the link entirely.
     */
    public function sizeChart()
    {
        return $this->belongsTo(SizeChart::class);
    }

    public function publishedVariations()
    {
        return $this->variations()->where('status', ProductVariation::STATUS_PUBLISH);
    }

    public function productAttributes()
    {
        return $this->hasMany(ProductAttribute::class)->orderBy('position')->orderBy('id');
    }

    /**
     * The main listing image: the one marked as featured, or the first one in
     * gallery order when none is marked.
     */
    public function mainImage(): ?ProductImage
    {
        $images = $this->images;

        if ($images->isEmpty()) {
            return null;
        }

        return $images->firstWhere('is_featured', true) ?? $images->first();
    }

    /**
     * The image listing cards swap to on hover. Prefers the image explicitly
     * marked as the second image, otherwise falls back to the second image in
     * gallery order, so products that were never configured still get the
     * behaviour as soon as a second image exists.
     */
    public function hoverImage(): ?ProductImage
    {
        $images = $this->images;

        if ($images->count() < 2) {
            return null;
        }

        $mainId = $this->mainImage()?->id;

        $second = $images->firstWhere('is_secondary', true);

        if ($second && $second->id !== $mainId) {
            return $second;
        }

        return $images->first(fn (ProductImage $image) => $image->id !== $mainId);
    }

    /**
     * The attributes that are used to build variations, in display order.
     */
    public function variationAttributes()
    {
        return $this->productAttributes()->where('is_variation', true);
    }

    public function isVariable(): bool
    {
        return $this->product_type === 'variable';
    }

    public function getDisplayPriceAttribute()
    {
        if ($this->isVariable()) {
            return $this->min_price !== null ? (float) $this->min_price : null;
        }

        return $this->getDisplayPrice();
    }

    /**
     * The price shown on listing pages: a range for variable products.
     */
    public function getPriceRangeAttribute(): ?array
    {
        if (! $this->isVariable()) {
            $price = $this->getDisplayPrice();
            return $price === null ? null : ['min' => (float) $price, 'max' => (float) $price];
        }

        if ($this->min_price === null) {
            return null;
        }

        return ['min' => (float) $this->min_price, 'max' => (float) $this->max_price];
    }

    public function isInStock(): bool
    {
        if ($this->isVariable()) {
            return $this->publishedVariations()->get()->contains(fn (ProductVariation $v) => $v->isInStock());
        }

        return ($this->stock_status ?: 'instock') !== 'outofstock' && $this->stock_quantity > 0;
    }

    public function getDisplayPrice()
    {
        if ($this->sale_price !== null) {
            return $this->sale_price;
        }
        return $this->regular_price;
    }

    public function updateStockFromVariations()
    {
        if ($this->product_type === 'variable') {
            $totalStock = (int) $this->publishedVariations()->sum('stock_quantity');
            $this->stock_quantity = $totalStock;
            $this->saveQuietly();
        }
    }

    protected static function booted()
    {
        static::saving(function ($product) {
            // Renaming a product re-derives the slug, otherwise the URL keeps
            // advertising the old title forever. Only when the name actually
            // changed, so re-saving an untouched product cannot rewrite a slug
            // that is already published.
            if ($product->isDirty('name')) {
                $fromName = Str::slug($product->name ?? '');

                // A title made entirely of symbols slugifies to nothing, and an
                // empty slug would produce a broken product URL. Keep the
                // current one rather than storing that.
                if ($fromName !== '') {
                    $product->slug = $fromName;
                }
            }

            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            } else {
                $product->slug = Str::slug($product->slug);
            }

            // Ensure uniqueness
            $baseSlug = $product->slug;
            $counter = 1;
            while (static::where('slug', $product->slug)->where('id', '!=', $product->id)->exists()) {
                $product->slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            if ($product->regular_price !== null && $product->regular_price < 0) {
                $product->regular_price = 0;
            }
            if ($product->sale_price !== null && $product->sale_price < 0) {
                $product->sale_price = 0;
            }
            if ($product->cost_price !== null && $product->cost_price < 0) {
                $product->cost_price = 0;
            }

            if ($product->manage_stock && $product->stock_quantity !== null && $product->stock_quantity <= 0) {
                $product->stock_status = 'outofstock';
            } elseif (! $product->isDirty('stock_status')) {
                $product->stock_status = $product->stock_status ?: 'instock';
            }
        });
    }
}
