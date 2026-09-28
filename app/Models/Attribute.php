<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Attribute extends Model
{
    public const TYPE_SELECT = 'select';
    public const TYPE_COLOR = 'color';
    public const TYPE_IMAGE = 'image';
    public const TYPE_BUTTON = 'button';

    public const ORDER_BY = ['custom', 'name', 'id'];

    protected $fillable = ['name', 'slug', 'type', 'order_by'];

    public function values()
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order')->orderBy('id');
    }

    public function productAttributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function getDisplayTypeAttribute(): string
    {
        return $this->type ?: self::TYPE_SELECT;
    }

    public function isSwatch(): bool
    {
        return in_array($this->type, [self::TYPE_COLOR, self::TYPE_IMAGE], true);
    }

    protected static function booted()
    {
        static::saving(function (self $attribute) {
            $attribute->slug = Str::slug($attribute->slug ?: $attribute->name);
            $attribute->type = $attribute->type ?: self::TYPE_SELECT;
            $attribute->order_by = in_array($attribute->order_by, self::ORDER_BY, true) ? $attribute->order_by : 'name';

            $base = $attribute->slug;
            $counter = 1;
            while (static::where('slug', $attribute->slug)->where('id', '!=', $attribute->id)->exists()) {
                $attribute->slug = "{$base}-{$counter}";
                $counter++;
            }
        });
    }
}
