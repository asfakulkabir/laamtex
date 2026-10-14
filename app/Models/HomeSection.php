<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    use HasFactory;

    public const LAYOUT_SLIDER = 'slider';

    public const LAYOUT_GRID = 'grid';

    public const CONTENT_CATEGORY = 'category';

    public const CONTENT_FEATURED = 'featured';

    public const CONTENT_LATEST = 'latest';

    /** Keeps a runaway limit from stalling the home page. */
    public const MAX_PRODUCT_LIMIT = 24;

    protected $fillable = [
        'title',
        'subtitle',
        'content_type',
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

    public function isCategory(): bool
    {
        return $this->content_type === self::CONTENT_CATEGORY || $this->content_type === null;
    }

    public function isFeatured(): bool
    {
        return $this->content_type === self::CONTENT_FEATURED;
    }

    public function isLatest(): bool
    {
        return $this->content_type === self::CONTENT_LATEST;
    }

    /**
     * Human label for the admin list and form hints.
     */
    public function contentTypeLabel(): string
    {
        return match ($this->content_type) {
            self::CONTENT_FEATURED => 'Featured products',
            self::CONTENT_LATEST => 'Latest products',
            default => 'Category products',
        };
    }

    /**
     * Content types an admin can pick for a section.
     *
     * @return array<string,string>
     */
    public static function contentTypes(): array
    {
        return [
            self::CONTENT_CATEGORY => 'Products from a category',
            self::CONTENT_FEATURED => 'Featured products',
            self::CONTENT_LATEST => 'Latest products',
        ];
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
     * The active products this section shows. Category and featured sections
     * follow the manual sort order the product list uses, while a latest
     * section is purely newest-first.
     */
    public function products()
    {
        $query = Product::with(['images', 'categories'])->where('is_active', true);

        if ($this->isLatest()) {
            return $query->latest('created_at')->limit($this->effective_limit)->get();
        }

        $query->orderBy('sort_order')->latest();

        if ($this->isFeatured()) {
            $query->where('is_featured', true);
        } elseif ($this->isCategory()) {
            $categoryIds = $this->categoryIds();

            if ($categoryIds !== []) {
                $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds));
            }
        }

        return $query->limit($this->effective_limit)->get();
    }
}
