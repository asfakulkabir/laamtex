<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariationAttributeValue extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_variation_id',
        'product_attribute_id',
        'attribute_value_id',
        'custom_value',
    ];

    public function productVariation()
    {
        return $this->belongsTo(ProductVariation::class);
    }

    public function productAttribute()
    {
        return $this->belongsTo(ProductAttribute::class);
    }

    public function attributeValue()
    {
        return $this->belongsTo(AttributeValue::class);
    }

    public function isAny(): bool
    {
        return $this->attribute_value_id === null && ($this->custom_value === null || $this->custom_value === '');
    }

    /**
     * The selected option name, or null when this variation is "Any".
     */
    public function displayValue(): ?string
    {
        if ($this->attributeValue) {
            return $this->attributeValue->name;
        }

        return $this->custom_value !== '' ? $this->custom_value : null;
    }
}
