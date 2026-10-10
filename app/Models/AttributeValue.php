<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttributeValue extends Model
{
    protected $fillable = ['attribute_id', 'name', 'slug', 'color_code', 'image', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function productAttributeValues()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function variationValues()
    {
        return $this->hasMany(VariationAttributeValue::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    public function getSwatchColorAttribute(): ?string
    {
        if ($this->color_code) {
            return $this->color_code;
        }

        return strtolower($this->name);
    }

    protected static function booted()
    {
        static::saving(function (self $value) {
            $value->slug = Str::slug($value->slug ?: $value->name);
            $value->sort_order = $value->sort_order ?? 0;

            if ($value->attribute && $value->attribute->type === Attribute::TYPE_COLOR) {
                $value->color_code = $value->color_code ?: self::guessColorCode($value->name);
            }

            $base = $value->slug;
            $counter = 1;
            while (
                static::where('attribute_id', $value->attribute_id)
                    ->where('slug', $value->slug)
                    ->where('id', '!=', $value->id)
                    ->exists()
            ) {
                $value->slug = "{$base}-{$counter}";
                $counter++;
            }
        });

        static::deleting(function (self $value) {
            if ($value->image && ! Media::isLibraryPath($value->image)) {
                Storage::disk('public')->delete($value->image);
            }
        });
    }

    /**
     * Best-effort swatch colour for a common colour name.
     */
    public static function guessColorCode(string $name): ?string
    {
        $named = [
            'black' => '#000000', 'white' => '#ffffff', 'red' => '#ff0000', 'blue' => '#0000ff',
            'green' => '#008000', 'yellow' => '#ffff00', 'orange' => '#ffa500', 'purple' => '#800080',
            'pink' => '#ffc0cb', 'grey' => '#808080', 'gray' => '#808080', 'brown' => '#a52a2a',
            'navy' => '#000080', 'beige' => '#f5f5dc', 'maroon' => '#800000', 'cyan' => '#00ffff',
            'magenta' => '#ff00ff', 'gold' => '#ffd700', 'silver' => '#c0c0c0', 'khaki' => '#f0e68c',
        ];

        return $named[strtolower(trim($name))] ?? null;
    }
}
