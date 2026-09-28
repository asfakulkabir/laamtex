<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_variations')) {
            return;
        }

        $legacy = DB::table('product_variations')
            ->select('id', 'product_id', 'size', 'color', 'weight', 'price', 'stock')
            ->get();

        if ($legacy->isEmpty()) {
            return;
        }

        $attributeCache = [];

        foreach ($legacy as $row) {
            $values = array_filter([
                'Size' => $row->size,
                'Color' => $row->color,
            ], fn ($v) => $v !== null && $v !== '');

            if ($values === []) {
                continue;
            }

            $productAttributes = [];

            foreach ($values as $attributeName => $valueName) {
                $attribute = $this->findOrCreateAttribute($attributeCache, $attributeName);
                $attributeValue = $this->findOrCreateValue($attribute, $valueName);

                $productAttributeId = DB::table('product_attributes')->where('product_id', $row->product_id)
                    ->where('attribute_id', $attribute->id)
                    ->value('id');

                if (!$productAttributeId) {
                    $position = DB::table('product_attributes')->where('product_id', $row->product_id)->count();
                    $productAttributeId = DB::table('product_attributes')->insertGetId([
                        'product_id' => $row->product_id,
                        'attribute_id' => $attribute->id,
                        'position' => $position,
                        'is_visible' => 1,
                        'is_variation' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $productAttributes[] = [
                    'id' => $productAttributeId,
                    'attribute_id' => $attribute->id,
                    'value_id' => $attributeValue->id,
                ];
            }

            $comboKey = collect($productAttributes)
                ->sortBy('value_id')
                ->pluck('value_id')
                ->implode('-');

            if ($comboKey === '') {
                // A row with neither size nor color can never match a unique
                // combination, so it is left without a combo key.
                $comboKey = null;
            }

            DB::table('product_variations')->where('id', $row->id)->update([
                'regular_price' => $row->price,
                'stock_quantity' => $row->stock,
                'manage_stock' => 1,
                'stock_status' => $row->stock > 0 ? 'instock' : 'outofstock',
                'combo_key' => $comboKey,
                'updated_at' => now(),
            ]);

            foreach ($productAttributes as $pa) {
                DB::table('variation_attribute_values')->insert([
                    'product_variation_id' => $row->id,
                    'product_attribute_id' => $pa['id'],
                    'attribute_value_id' => $pa['value_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->refreshProductPriceRanges();
    }

    public function down(): void
    {
        // The new attribute rows are additive; legacy size/color columns are
        // left untouched so the previous behaviour can be restored manually.
    }

    private function findOrCreateAttribute(array &$cache, string $name)
    {
        $slug = Str::slug($name);

        if (isset($cache[$slug])) {
            return $cache[$slug];
        }

        $attribute = DB::table('attributes')->where('slug', $slug)->first();

        if (!$attribute) {
            $id = DB::table('attributes')->insertGetId([
                'name' => $name,
                'slug' => $slug,
                'type' => strtolower($name) === 'color' ? 'color' : 'select',
                'order_by' => 'name',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $attribute = DB::table('attributes')->where('id', $id)->first();
        }

        return $cache[$slug] = $attribute;
    }

    private function findOrCreateValue($attribute, string $name)
    {
        $slug = Str::slug($name);

        $value = DB::table('attribute_values')
            ->where('attribute_id', $attribute->id)
            ->where('slug', $slug)
            ->first();

        if ($value) {
            return $value;
        }

        $sortOrder = DB::table('attribute_values')->where('attribute_id', $attribute->id)->max('sort_order') ?? 0;

        $id = DB::table('attribute_values')->insertGetId([
            'attribute_id' => $attribute->id,
            'name' => $name,
            'slug' => $slug,
            'color_code' => $attribute->type === 'color' ? $this->guessColorCode($name) : null,
            'sort_order' => $sortOrder + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('attribute_values')->where('id', $id)->first();
    }

    private function guessColorCode(string $name): ?string
    {
        $named = [
            'black' => '#000000', 'white' => '#ffffff', 'red' => '#ff0000', 'blue' => '#0000ff',
            'green' => '#008000', 'yellow' => '#ffff00', 'orange' => '#ffa500', 'purple' => '#800080',
            'pink' => '#ffc0cb', 'grey' => '#808080', 'gray' => '#808080', 'brown' => '#a52a2a',
            'navy' => '#000080', 'beige' => '#f5f5dc', 'maroon' => '#800000', 'cyan' => '#00ffff',
            'magenta' => '#ff00ff', 'gold' => '#ffd700', 'silver' => '#c0c0c0', 'khaki' => '#f0e68c',
        ];

        return $named[strtolower($name)] ?? null;
    }

    private function refreshProductPriceRanges(): void
    {
        DB::table('products')->orderBy('id')->chunk(100, function ($products) {
            foreach ($products as $product) {
                if ($product->product_type !== 'variable') {
                    continue;
                }

                $variations = DB::table('product_variations')
                    ->where('product_id', $product->id)
                    ->where('status', 'publish')
                    ->get();

                $prices = $variations
                    ->map(function ($variation) {
                        $regular = $variation->regular_price === null ? null : (float) $variation->regular_price;
                        $sale = $variation->sale_price === null ? null : (float) $variation->sale_price;

                        $onSale = $sale !== null && $regular !== null && $sale < $regular;
                        $price = $onSale ? $sale : $regular;

                        $quantity = $variation->manage_stock
                            ? $variation->stock_quantity
                            : $product->stock_quantity;

                        $inStock = $variation->stock_status
                            ?: ($quantity !== null && $quantity <= 0 ? 'outofstock' : 'instock');

                        return $inStock === 'outofstock' ? null : $price;
                    })
                    ->filter(fn ($price) => $price !== null);

                DB::table('products')->where('id', $product->id)->update([
                    'min_price' => $prices->isEmpty() ? null : $prices->min(),
                    'max_price' => $prices->isEmpty() ? null : $prices->max(),
                ]);
            }
        });
    }
};
