<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    protected $fillable = [
        'product_id',
        'attribute_id',
        'custom_name',
        'custom_options',
        'position',
        'is_visible',
        'is_variation',
    ];

    protected $casts = [
        'custom_options' => 'array',
        'position' => 'integer',
        'is_visible' => 'boolean',
        'is_variation' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function values()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_values')
            ->withPivot('id')
            ->orderBy('attribute_values.sort_order')
            ->orderBy('attribute_values.id');
    }

    public function isCustom(): bool
    {
        return $this->attribute_id === null;
    }

    /**
     * The admin-facing name, which falls back to the custom name.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->isCustom()) {
            return (string) $this->custom_name;
        }

        return (string) ($this->attribute->name ?? $this->custom_name ?? '');
    }

    /**
     * The type of the underlying global attribute, or select for custom ones.
     */
    public function getTypeAttribute(): string
    {
        return $this->isCustom() ? Attribute::TYPE_SELECT : (string) ($this->attribute->type ?? Attribute::TYPE_SELECT);
    }

    /**
     * The option names offered by this attribute, in display order.
     */
    public function getOptionNamesAttribute(): array
    {
        if ($this->isCustom()) {
            return array_values(array_filter((array) $this->custom_options));
        }

        return $this->values->pluck('name')->all();
    }

    public function isSwatch(): bool
    {
        return in_array($this->type, [Attribute::TYPE_COLOR, Attribute::TYPE_IMAGE], true);
    }
}
