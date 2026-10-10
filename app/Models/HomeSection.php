<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    use HasFactory;

    public const LAYOUT_SLIDER = 'slider';

    public const LAYOUT_GRID = 'grid';

    /** Keeps a runaway limit from stalling the home page. */
    public const MAX_PRODUCT_LIMIT = 24;

    protected $fillable = [
        'title',
        'subtitle',
        'category_id',
        'layout',
        'product_limit',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'product_limit' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function isSlider(): bool
    {
        return $this->layout === self::LAYOUT_SLIDER;
    }

    /**
     * Clamped so an admin typo cannot ask the database for 100000 rows.
     */
    public function getEffectiveLimitAttribute(): int
    {
        $limit = (int) $this->product_limit;

        if ($limit < 1) {
            return 8;
        }

        return min($limit, self::MAX_PRODUCT_LIMIT);
    }

    /**
     * The category itself plus every nested child, so selecting "Tops" also
     * shows products filed under "Tops > T-Shirts".
     */
    public function categoryIds(): array
    {
        if ($this->category_id === null) {
            return [];
        }

        $category = $this->category;

        if (! $category) {
            return [];
        }

        return $this->descendantIds($category);
    }

    private function descendantIds(Category $category): array
    {
        $ids = [$category->id];

        foreach ($category->children as $child) {
            $ids = array_merge($ids, $this->descendantIds($child));
        }

        return $ids;
    }

    /**
     * The active products this section shows, newest first within the manual
     * sort order the product list uses.
     */
    public function products()
    {
        $query = Product::with(['images', 'categories'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->latest();

        $categoryIds = $this->categoryIds();

        if ($categoryIds !== []) {
            $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds));
        }

        return $query->limit($this->effective_limit)->get();
    }
}
