<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'size',
        'weight',
        'color',
        'price',
        'stock',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted()
    {
        static::saved(function ($variation) {
            if ($variation->product) {
                $variation->product->updateStockFromVariations();
            }
        });

        static::deleted(function ($variation) {
            if ($variation->product) {
                $variation->product->updateStockFromVariations();
            }
        });
    }
}
