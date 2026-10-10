<?php

namespace App\Services;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\Media;
use App\Models\ProductVariation;
use App\Models\VariationAttributeValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VariationService
{
    /**
     * Sentinel used by the admin form and the combo key for a wildcard
     * ("Any value") slot. Real attribute values are always positive ids.
     */
    public const ANY_VALUE = -1;

    /**
     * Replace a product's attributes from the admin form payload.
     *
     * Expected shape per row:
     * [
     *   'attribute_id' => 3|null, 'custom_name' => 'Material',
     *   'options' => ['Cotton','Silk'] or "Cotton|Silk",
     *   'is_visible' => 1, 'is_variation' => 1, 'value_ids' => [1,2],
     * ]
     */
    public function syncAttributes(Product $product, array $rows): void
    {
        $keptIds = [];

        foreach (array_values($rows) as $position => $row) {
            $attributeId = ! empty($row['attribute_id']) ? (int) $row['attribute_id'] : null;
            $customName = trim((string) ($row['custom_name'] ?? ''));

            if ($attributeId === null && $customName === '') {
                continue;
            }

            $attribute = $attributeId ? Attribute::find($attributeId) : null;
            if ($attributeId !== null && ! $attribute) {
                continue;
            }

            $customOptions = $this->parseOptions($row['options'] ?? []);

            if ($attribute === null && $customOptions === []) {
                continue;
            }

            $productAttribute = ProductAttribute::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'attribute_id' => $attribute?->id,
                    'custom_name' => $attribute ? null : $customName,
                ],
                [
                    'custom_options' => $attribute ? null : $customOptions,
                    'position' => $position,
                    'is_visible' => (bool) ($row['is_visible'] ?? false),
                    'is_variation' => (bool) ($row['is_variation'] ?? false),
                ]
            );

            $keptIds[] = $productAttribute->id;

            $this->syncProductAttributeValues($productAttribute, $row, $customOptions);
        }

        // An empty or unusable payload is far more likely to be a form that did
        // not send its attributes than a deliberate "delete them all". Pruning
        // here would cascade away every variation, and with them any order
        // history, so leave the existing attributes untouched instead.
        if ($keptIds === []) {
            return;
        }

        // Drop attributes that were removed in the UI. Their variation rows
        // cascade away with them.
        $product->productAttributes()->whereNotIn('id', $keptIds)->get()->each->delete();

        $product->unsetRelation('productAttributes');
        $this->refreshPriceRange($product);
    }

    /**
     * Attach the selected option values to a product attribute, creating
     * attribute values on the fly for custom attributes and brand new terms.
     */
    private function syncProductAttributeValues(ProductAttribute $productAttribute, array $row, array $customOptions): void
    {
        $selected = $this->parseValueIds($row['value_ids'] ?? []);

        if ($productAttribute->isCustom()) {
            $keep = [];

            foreach ($customOptions as $name) {
                $existing = $productAttribute->values()->where('name', $name)->first();
                $value = $existing ?: $this->createLooseValue($productAttribute, $name);

                if ($value) {
                    $keep[] = $value->id;
                }
            }

            $productAttribute->values()->sync($keep);
            $productAttribute->load('values');
            $this->pruneLooseValues($productAttribute, $keep);

            return;
        }

        $valid = $productAttribute->attribute->values()->pluck('id')->all();
        $keep = array_values(array_intersect($selected, $valid));

        // Terms typed straight into the field create new global values.
        foreach ($this->parseOptions($row['new_options'] ?? []) as $name) {
            $value = $this->createGlobalValue($productAttribute->attribute_id, $name);

            if ($value && ! in_array($value->id, $keep, true)) {
                $keep[] = $value->id;
            }
        }

        $productAttribute->values()->sync($keep);
        $productAttribute->load('values');
    }

    private function createGlobalValue(int $attributeId, string $name): ?AttributeValue
    {
        $name = trim($name);

        if ($name === '') {
            return null;
        }

        $attribute = Attribute::find($attributeId);
        if (! $attribute) {
            return null;
        }

        $existing = $attribute->values()->where('slug', Str::slug($name))->first();

        if ($existing) {
            return $existing;
        }

        return $attribute->values()->create([
            'name' => $name,
            'color_code' => $attribute->type === Attribute::TYPE_COLOR ? $this->guessColorCode($name) : null,
            'sort_order' => (int) ($attribute->values()->max('sort_order') ?? 0) + 1,
        ]);
    }

    /**
     * Custom attributes have no global attribute, so their values are stored
     * as attribute values attached to a lightweight placeholder attribute.
     */
    private function createLooseValue(ProductAttribute $productAttribute, string $name): ?AttributeValue
    {
        return $this->createGlobalValue($this->looseAttributeId(), $name);
    }

    private function looseAttributeId(): int
    {
        $attribute = Attribute::firstOrCreate(
            ['slug' => 'custom-attributes'],
            ['name' => 'Custom Attributes', 'type' => Attribute::TYPE_SELECT, 'order_by' => 'name']
        );

        return (int) $attribute->id;
    }

    private function pruneLooseValues(ProductAttribute $productAttribute, array $keepIds): void
    {
        $looseId = $this->looseAttributeId();

        AttributeValue::where('attribute_id', $looseId)
            ->whereNotIn('id', $keepIds ?: [0])
            ->whereDoesntHave('productAttributeValues')
            ->whereDoesntHave('variationValues')
            ->get()
            ->each->delete();
    }

    /**
     * Create every missing combination of the variation attributes.
     * Safe to run repeatedly: existing combinations are skipped.
     *
     * @return array{created:int,skipped:int}
     */
    public function generateVariations(Product $product, array $defaults = []): array
    {
        $attributes = $product->variationAttributes()->with('values')->get();

        if ($attributes->isEmpty() || $attributes->contains(fn (ProductAttribute $a) => $a->values->isEmpty())) {
            return ['created' => 0, 'skipped' => 0];
        }

        $existingKeys = $product->variations()->pluck('combo_key')->all();
        $created = 0;
        $skipped = 0;
        $menuOrder = (int) $product->variations()->max('menu_order');

        foreach ($this->combinations($attributes) as $combination) {
            $comboKey = $this->comboKey($combination);

            if (in_array($comboKey, $existingKeys, true)) {
                $skipped++;
                continue;
            }

            $variation = $product->variations()->create([
                'sku' => $this->generateSku($product, $combination),
                'combo_key' => $comboKey,
                'status' => ProductVariation::STATUS_PUBLISH,
                'manage_stock' => true,
                'stock_quantity' => 0,
                'stock_status' => ProductVariation::STOCK_OUT_OF_STOCK,
                'regular_price' => $product->regular_price,
                'menu_order' => ++$menuOrder,
            ]);

            $this->attachValues($variation, $combination, $defaults);

            $existingKeys[] = $comboKey;
            $created++;
        }

        $this->refreshPriceRange($product);

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * The cartesian product of the values of every variation attribute.
     *
     * @return array<int, array<int, array{product_attribute_id:int, attribute_value_id:int}>>
     */
    private function combinations(Collection $attributes): array
    {
        $result = [[]];

        foreach ($attributes as $attribute) {
            $next = [];

            foreach ($result as $partial) {
                foreach ($attribute->values as $value) {
                    $next[] = array_merge($partial, [[
                        'product_attribute_id' => $attribute->id,
                        'attribute_value_id' => $value->id,
                    ]]);
                }
            }

            $result = $next;
        }

        return $result;
    }

    /**
     * A stable key for a combination, built from the selected value ids.
     */
    public function comboKey(array $combination): string
    {
        $ids = array_map(
            fn ($item) => $item['attribute_value_id'] === null
                ? self::ANY_VALUE
                : (int) $item['attribute_value_id'],
            $combination
        );
        sort($ids);

        return implode('-', $ids);
    }

    private function attachValues(ProductVariation $variation, array $combination, array $defaults = []): void
    {
        foreach ($combination as $item) {
            $valueId = $defaults[(int) $item['product_attribute_id']] ?? $item['attribute_value_id'];

            if ($valueId === self::ANY_VALUE) {
                $valueId = null;
            }

            VariationAttributeValue::create([
                'product_variation_id' => $variation->id,
                'product_attribute_id' => $item['product_attribute_id'],
                'attribute_value_id' => $valueId,
            ]);
        }
    }

    private function generateSku(Product $product, array $combination): string
    {
        $parts = [$product->sku ?: Str::upper(Str::substr((string) $product->name, 0, 6))];

        $productAttributes = ProductAttribute::whereIn('id', array_column($combination, 'product_attribute_id'))
            ->get()
            ->keyBy('id');
        $valueIds = array_column($combination, 'attribute_value_id');
        $names = AttributeValue::whereIn('id', $valueIds)->get()->keyBy('id');

        $pieces = [];
        foreach ($combination as $item) {
            $value = $names[$item['attribute_value_id']] ?? null;
            $attribute = $productAttributes[$item['product_attribute_id']] ?? null;
            $prefix = Str::upper(Str::substr((string) $attribute?->display_name, 0, 2));
            $pieces[] = trim($prefix . Str::upper(Str::substr((string) $value?->name, 0, 4)), '-');
        }

        $parts[] = implode('-', $pieces);

        $sku = implode('-', $parts);

        return $this->uniqueSku($sku);
    }

    private function uniqueSku(string $sku): string
    {
        $base = $sku;
        $counter = 1;

        while ($this->skuExists($sku)) {
            $sku = $base . '-' . $counter++;
        }

        return $sku;
    }

    public function skuExists(?string $sku, ?int $ignoreVariationId = null): bool
    {
        if ($sku === null || $sku === '') {
            return false;
        }

        $onProduct = Product::where('sku', $sku)->exists();
        if ($onProduct) {
            return true;
        }

        return ProductVariation::where('sku', $sku)
            ->when($ignoreVariationId, fn ($q) => $q->where('id', '!=', $ignoreVariationId))
            ->exists();
    }

    /**
     * Persist the variations submitted from the admin Variations tab.
     */
    /**
     * Combinations that appear more than once in a single submit.
     *
     * Two rows for the same combination cannot both exist, so saving silently
     * keeps the last one and discards the other. The admin is told instead.
     *
     * @return array<int, string> Human readable labels, e.g. "Pink / S".
     */
    public function duplicateCombinationLabels(Product $product, array $rows): array
    {
        $variationAttributes = $product->variationAttributes()->with('values')->get();
        $seen = [];
        $duplicates = [];

        foreach (array_values($rows) as $row) {
            $selected = $this->normaliseSelection($row['values'] ?? [], $variationAttributes);

            if ($selected === []) {
                continue;
            }

            $comboKey = $this->comboKey($selected);

            if (isset($seen[$comboKey])) {
                $duplicates[] = $this->describeCombination($selected, $variationAttributes);
                continue;
            }

            $seen[$comboKey] = true;
        }

        return array_values(array_unique($duplicates));
    }

    /**
     * @return string The option names in a combination, e.g. "Pink / S".
     */
    private function describeCombination(array $selected, Collection $attributes): string
    {
        $parts = [];

        foreach ($selected as $item) {
            $attribute = $attributes->firstWhere('id', $item['product_attribute_id']);
            $value = null;
            if ($item['attribute_value_id'] !== null) {
                $value = $attribute?->values->firstWhere('id', $item['attribute_value_id'])
                    ?? $attribute?->attribute?->values->firstWhere('id', $item['attribute_value_id'])
                    ?? AttributeValue::find($item['attribute_value_id']);
            }

            $parts[] = $value?->name ?? 'Any';
        }

        return implode(' / ', $parts);
    }

    public function saveVariations(Product $product, array $rows, array $uploadedImages = []): void
    {
        $variationAttributes = $product->variationAttributes()->with('values')->get();
        $keptIds = [];

        foreach (array_values($rows) as $index => $row) {
            $selected = $this->normaliseSelection($row['values'] ?? [], $variationAttributes);

            if ($selected === []) {
                continue;
            }

            $comboKey = $this->comboKey($selected);

            $variation = null;

            if (! empty($row['id'])) {
                $variation = $product->variations()->where('id', $row['id'])->first();
            }

            $existingWithCombo = $product->variations()->where('combo_key', $comboKey)->first();

            if ($existingWithCombo !== null && ($variation === null || $variation->id !== $existingWithCombo->id)) {
                $variation = $existingWithCombo;
            } elseif ($variation === null) {
                $variation = $product->variations()->create([
                    'combo_key' => $comboKey,
                    'menu_order' => $index,
                ]);
            } else {
                $variation->combo_key = $comboKey;
            }

            $sku = trim((string) ($row['sku'] ?? ''));
            if ($sku !== '' && $this->skuExists($sku, $variation->id)) {
                $sku = $this->uniqueSku($sku);
            }

            $regularPrice = $this->toDecimal($row['regular_price'] ?? null);
            $salePrice = $this->toDecimal($row['sale_price'] ?? null);

            if ($salePrice !== null && $regularPrice !== null && $salePrice >= $regularPrice) {
                $salePrice = null;
            }

            $manageStock = (bool) ($row['manage_stock'] ?? false);

            $variation->update([
                'sku' => $sku !== '' ? $sku : $variation->sku,
                'status' => ($row['status'] ?? ProductVariation::STATUS_PUBLISH) === ProductVariation::STATUS_PRIVATE
                    ? ProductVariation::STATUS_PRIVATE
                    : ProductVariation::STATUS_PUBLISH,
                'description' => $row['description'] ?? null,
                'regular_price' => $regularPrice,
                'sale_price' => $salePrice,
                'manage_stock' => $manageStock,
                'stock_quantity' => $manageStock ? max(0, (int) ($row['stock_quantity'] ?? 0)) : null,
                'stock_status' => $row['stock_status'] ?? ProductVariation::STOCK_IN_STOCK,
                'weight_value' => $this->toDecimal($row['weight_value'] ?? null, 3),
                'menu_order' => $index,
            ]);

            $this->handleImage($variation, $row, $uploadedImages[$index] ?? null);
            $this->syncVariationValues($variation, $selected);

            $keptIds[] = $variation->id;
        }

        // Rows that resolve to no valid selection are dropped above. If that
        // left nothing at all, the submitted values no longer match the
        // product's attributes, which is not the same as the admin asking for
        // every row to be deleted. Deleting here would silently wipe the whole
        // variation set, so it is left alone.
        if ($keptIds === []) {
            $this->refreshPriceRange($product);

            return;
        }

        // Rows removed in the UI are deleted, unless they are referenced by
        // orders. Those are disabled instead so old orders stay intact.
        $product->variations()->whereNotIn('id', $keptIds)->get()->each(function (ProductVariation $variation) {
            if ($variation->orderItems()->exists()) {
                $variation->update(['status' => ProductVariation::STATUS_PRIVATE]);
            } else {
                $variation->delete();
            }
        });

        $this->refreshPriceRange($product);
    }

    private function handleImage(ProductVariation $variation, array $row, $uploadedImage): void
    {
        if ($uploadedImage) {
            $path = $uploadedImage->store('product_variations', 'public');

            if ($variation->image && $variation->image !== $path && ! Media::isLibraryPath($variation->image)) {
                Storage::disk('public')->delete($variation->image);
            }

            $variation->image = $path;
        } elseif (! empty($row['image_media_id'])) {
            // The image was picked from the media library rather than uploaded
            // with this form, so reuse the file that is already stored.
            $picked = Media::find($row['image_media_id']);

            if ($picked && $picked->isImage() && Storage::disk($picked->disk ?: 'public')->exists($picked->path)) {
                if ($variation->image && $variation->image !== $picked->path && ! Media::isLibraryPath($variation->image)) {
                    Storage::disk('public')->delete($variation->image);
                }

                $variation->image = $picked->path;
            }
        }

        if (($row['remove_image'] ?? false) && $variation->image) {
            if (! Media::isLibraryPath($variation->image)) {
                Storage::disk('public')->delete($variation->image);
            }
            $variation->image = null;
        }

        if ($variation->isDirty('image')) {
            $variation->save();
        }
    }

    private function syncVariationValues(ProductVariation $variation, array $selected): void
    {
        $variation->attributeValues()->delete();

        foreach ($selected as $item) {
            VariationAttributeValue::create([
                'product_variation_id' => $variation->id,
                'product_attribute_id' => $item['product_attribute_id'],
                'attribute_value_id' => $item['attribute_value_id'],
            ]);
        }

        $variation->load('attributeValues');
    }

    /**
     * Turn the submitted attribute selection into a validated combination.
     * Unknown or missing values are dropped.
     */
    /**
     * Turn the submitted attribute selection into a validated combination.
     *
     * An attribute that is missing from the payload entirely is dropped, while
     * an attribute submitted with an empty value is kept as a wildcard ("Any
     * value") row. Unknown value ids are dropped.
     */
    private function normaliseSelection($raw, Collection $attributes): array
    {
        $raw = is_array($raw) ? $raw : [];

        $selection = [];

        foreach ($attributes as $attribute) {
            $key = $this->selectionKey($attribute, $raw);

            if ($key === null) {
                continue;
            }

            $valueId = (int) $raw[$key];

            // "— Any —" keeps the attribute in the combination but with no
            // value, which the storefront treats as a wildcard.
            if ($valueId <= 0 || $valueId === self::ANY_VALUE) {
                $selection[] = [
                    'product_attribute_id' => $attribute->id,
                    'attribute_value_id' => null,
                ];
                continue;
            }

            if (! $attribute->values->contains('id', $valueId)) {
                if ($attribute->attribute && $attribute->attribute->values->contains('id', $valueId)) {
                    $selection[] = [
                        'product_attribute_id' => $attribute->id,
                        'attribute_value_id' => $valueId,
                    ];
                    $attribute->values()->syncWithoutDetaching([$valueId]);
                    continue;
                }
                continue;
            }

            $selection[] = [
                'product_attribute_id' => $attribute->id,
                'attribute_value_id' => $valueId,
            ];
        }

        return $selection;
    }

    /**
     * Which key a posted variation row used for this attribute.
     *
     * Rows normally key by product_attribute_id. The edit form also builds
     * combinations for an attribute the admin has just ticked but not yet
     * saved, and that one has no product_attribute_id to post, so it is keyed
     * "p" plus its position in the submitted attributes array. Positions and
     * ids overlap numerically, so the prefixed key is what keeps them apart.
     */
    private function selectionKey($attribute, array $raw): int|string|null
    {
        if (array_key_exists($attribute->id, $raw)) {
            return $attribute->id;
        }

        if (array_key_exists((string) $attribute->id, $raw)) {
            return (string) $attribute->id;
        }

        $positionKey = 'p' . (int) $attribute->position;

        return array_key_exists($positionKey, $raw) ? $positionKey : null;
    }

    /**
     * Recalculate the cached min/max price. Per spec this reflects only
     * active (publish) and purchasable variations, using their active price.
     */
    public function refreshPriceRange(Product $product): void
    {
        if (! $product->isVariable()) {
            return;
        }

        $prices = $product->publishedVariations()
            ->get()
            ->filter(fn (ProductVariation $v) => $v->isPurchasable())
            ->map(fn (ProductVariation $v) => $v->active_price)
            ->filter(fn ($price) => $price !== null)
            ->map(fn ($price) => (float) $price);

        $product->min_price = $prices->isEmpty() ? null : number_format($prices->min(), 2, '.', '');
        $product->max_price = $prices->isEmpty() ? null : number_format($prices->max(), 2, '.', '');
        $product->saveQuietly();
    }

    /**
     * Apply a bulk action to every variation in the payload.
     */
    public function applyBulkAction(Product $product, string $action, array $params, array $variationIds): int
    {
        $variations = $product->variations()->whereIn('id', $variationIds)->get();
        $affected = 0;

        foreach ($variations as $variation) {
            switch ($action) {
                case 'set_regular_price':
                    $variation->regular_price = $this->toDecimal($params['value'] ?? null);
                    break;
                case 'set_sale_price':
                    $variation->sale_price = $this->toDecimal($params['value'] ?? null);
                    break;
                case 'increase_price':
                    $variation->regular_price = $this->toDecimal($this->applyPriceDelta(
                        $variation->regular_price,
                        (float) ($params['value'] ?? 0),
                        $params['mode'] ?? 'fixed'
                    ));
                    break;
                case 'decrease_price':
                    $variation->regular_price = $this->toDecimal($this->applyPriceDelta(
                        $variation->regular_price,
                        -abs((float) ($params['value'] ?? 0)),
                        $params['mode'] ?? 'fixed'
                    ));
                    break;
                case 'set_stock':
                    $variation->manage_stock = true;
                    $variation->stock_quantity = max(0, (int) ($params['value'] ?? 0));
                    $variation->stock_status = $variation->stock_quantity > 0
                        ? ProductVariation::STOCK_IN_STOCK
                        : ProductVariation::STOCK_OUT_OF_STOCK;
                    break;
                case 'toggle_manage_stock':
                    $variation->manage_stock = ! $variation->manage_stock;
                    break;
                case 'enable':
                    $variation->status = ProductVariation::STATUS_PUBLISH;
                    break;
                case 'disable':
                    $variation->status = ProductVariation::STATUS_PRIVATE;
                    break;
                case 'set_weight':
                    $variation->weight_value = $this->toDecimal($params['value'] ?? null, 3);
                    break;
                default:
                    continue 2;
            }

            $variation->save();
            $affected++;
        }

        $this->refreshPriceRange($product);

        return $affected;
    }

    private function applyPriceDelta($current, float $amount, string $mode)
    {
        $base = (float) ($current ?? 0);

        $result = $mode === 'percent' ? $base + ($base * $amount / 100) : $base + $amount;

        return max(0, round($result, 2));
    }

    private function toNumber($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    /**
     * Money and weight are stored as decimal strings so Eloquent's decimal
     * cast never receives a float.
     */
    private function toDecimal($value, int $precision = 2): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return number_format((float) $value, $precision, '.', '');
    }

    private function parseOptions($value): array
    {
        if (is_array($value)) {
            $parts = $value;
        } else {
            $parts = preg_split('/[|,]/', (string) $value) ?: [];
        }

        $parts = array_map(fn ($part) => trim((string) $part), $parts);

        return array_values(array_filter($parts));
    }

    private function parseValueIds($value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', array_filter($value))));
    }

    private function guessColorCode(string $name): ?string
    {
        return AttributeValue::guessColorCode($name);
    }
}
