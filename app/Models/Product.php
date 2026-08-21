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
        'short_description',
        'description',
        'product_type',
        'regular_price',
        'sale_price',
        'stock_quantity',
        'is_active',
        'is_featured',
        'sort_order',
        'seo_title',
        'meta_description',
    ];

    protected $casts = [
        'regular_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
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
        return $this->hasMany(ProductVariation::class);
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
            $totalStock = $this->variations()->sum('stock') ?? 0;
            $this->stock_quantity = $totalStock;
            $this->saveQuietly();
        }
    }

    protected static function booted()
    {
        static::saving(function ($product) {
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
        });
    }
}
