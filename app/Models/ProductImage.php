<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image',
        'name',
        'alt_text',
        'is_featured',
        'order',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'order' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted()
    {
        static::deleted(function ($productImage) {
            if ($productImage->image) {
                Storage::disk('public')->delete($productImage->image);
            }
        });

        static::updating(function ($productImage) {
            $original = $productImage->getOriginal('image');
            if ($original && $original !== $productImage->image) {
                Storage::disk('public')->delete($original);
            }
        });
    }
}
