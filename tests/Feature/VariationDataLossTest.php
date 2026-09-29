<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\User;
use App\Services\VariationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A variation set and the attributes behind it must never be destroyed by a
 * payload that simply failed to carry them.
 */
class VariationDataLossTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    /** @return array{0: Product, 1: ProductAttribute} */
    private function product(string $name = 'Hair Dryer'): array
    {
        $admin = $this->admin();

        $color = Attribute::create(['name' => 'Color', 'type' => Attribute::TYPE_COLOR]);
        $pink = $color->values()->create(['name' => 'Pink', 'color_code' => '#ffc0cb']);
        $blue = $color->values()->create(['name' => 'Blue', 'color_code' => '#00f']);

        $product = Product::create([
            'user_id' => $admin->id,
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)).'-'.substr(md5($name), 0, 6),
            'product_type' => 'variable',
            'regular_price' => '100.00',
            'is_active' => true,
        ]);

        $productAttribute = $product->productAttributes()->create([
            'attribute_id' => $color->id,
            'position' => 1,
            'is_visible' => true,
            'is_variation' => true,
        ]);
        $productAttribute->values()->attach([$pink->id, $blue->id]);

        app(VariationService::class)->generateVariations($product);

        return [$product, $productAttribute];
    }

    public static function unusableAttributePayloads(): array
    {
        return [
            'empty array' => [[]],
            'row with nothing usable in it' => [[['attribute_id' => '', 'custom_name' => '', 'options' => '', 'value_ids' => []]]],
            'row naming a missing attribute' => [[['attribute_id' => 999999, 'value_ids' => []]]],
        ];
    }

    /**
     * @dataProvider unusableAttributePayloads
     */
    public function test_an_unusable_attribute_payload_keeps_attributes_and_variations(array $attributes)
    {
        [$product] = $this->product('Dryer '.md5(json_encode($attributes)));

        $variationCount = $product->variations()->count();
        $this->assertSame(1, $product->productAttributes()->count());
        $this->assertSame(2, $variationCount);

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'product_type' => 'variable',
            'regular_price' => '100.00',
            'is_active' => '1',
            'attributes' => $attributes,
            'variations' => [],
        ]);

        $this->assertSame(1, $product->productAttributes()->count(), 'attributes must survive an unusable payload');
        $this->assertSame($variationCount, $product->variations()->count(), 'variations must survive an unusable payload');
    }

    public function test_an_empty_variation_array_does_not_wipe_the_variation_set()
    {
        [$product] = $this->product('Dryer Empty Rows');

        $variationCount = $product->variations()->count();
        $this->assertSame(2, $variationCount);

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'product_type' => 'variable',
            'regular_price' => '100.00',
            'is_active' => '1',
            'attributes' => [],
            'variations' => [],
        ]);

        $this->assertSame(1, $product->productAttributes()->count());
        $this->assertSame($variationCount, $product->variations()->count());
    }

    public function test_variation_rows_that_no_longer_match_are_not_treated_as_a_delete()
    {
        [$product] = $this->product('Dryer Stale Rows');

        $variationCount = $product->variations()->count();

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'product_type' => 'variable',
            'regular_price' => '100.00',
            'is_active' => '1',
            'variations' => [
                ['values' => [999999 => 999999], 'regular_price' => '10', 'status' => 'publish'],
            ],
        ]);

        $this->assertSame($variationCount, $product->variations()->count());
    }

    public function test_a_normal_edit_still_saves_and_keeps_everything()
    {
        [$product, $productAttribute] = $this->product('Dryer Normal');

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'name' => 'Dryer Normal',
            'product_type' => 'variable',
            'regular_price' => '100.00',
            'is_active' => '1',
            'attributes' => [[
                'attribute_id' => $productAttribute->attribute_id,
                'value_ids' => $productAttribute->values->pluck('id')->all(),
                'is_visible' => '1',
                'is_variation' => '1',
            ]],
            'variations' => $product->variations->map(fn ($variation) => [
                'id' => $variation->id,
                'values' => $variation->attributeValues
                    ->mapWithKeys(fn ($value) => [$value->product_attribute_id => (string) $value->attribute_value_id])
                    ->all(),
                'regular_price' => '150',
                'manage_stock' => '1',
                'stock_quantity' => 4,
                'stock_status' => 'instock',
                'status' => 'publish',
            ])->values()->all(),
        ])->assertSessionHasNoErrors();

        $this->assertSame(2, $product->variations()->count());
        $this->assertSame(1, $product->productAttributes()->count());

        foreach ($product->variations()->get() as $variation) {
            $this->assertEquals('150.00', $variation->regular_price);
        }
    }

    public function test_removing_one_attribute_row_still_deletes_only_that_attribute()
    {
        $admin = $this->admin();

        $size = Attribute::create(['name' => 'Size', 'type' => Attribute::TYPE_SELECT]);
        $small = $size->values()->create(['name' => 'Small']);
        $color = Attribute::create(['name' => 'Color', 'type' => Attribute::TYPE_COLOR]);
        $red = $color->values()->create(['name' => 'Red', 'color_code' => '#f00']);

        $product = Product::create([
            'user_id' => $admin->id,
            'name' => 'Two Attrs',
            'slug' => 'two-attrs',
            'product_type' => 'variable',
            'regular_price' => '100.00',
            'is_active' => true,
        ]);

        $sizeAttribute = $product->productAttributes()->create([
            'attribute_id' => $size->id, 'position' => 1, 'is_visible' => true, 'is_variation' => true,
        ]);
        $sizeAttribute->values()->attach([$small->id]);
        $colorAttribute = $product->productAttributes()->create([
            'attribute_id' => $color->id, 'position' => 2, 'is_visible' => true, 'is_variation' => true,
        ]);
        $colorAttribute->values()->attach([$red->id]);

        $service = app(VariationService::class);
        $service->generateVariations($product);
        $this->assertSame(1, $product->variations()->count());

        // Keep Size, drop Color.
        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => 'Two Attrs',
            'product_type' => 'variable',
            'regular_price' => '100.00',
            'is_active' => '1',
            'attributes' => [[
                'attribute_id' => $size->id,
                'value_ids' => [$small->id],
                'is_visible' => '1',
                'is_variation' => '1',
            ]],
            'variations' => [[
                'id' => $product->variations()->first()->id,
                'values' => [$sizeAttribute->id => $small->id],
                'regular_price' => '100',
                'manage_stock' => '1',
                'stock_quantity' => 1,
                'stock_status' => 'instock',
                'status' => 'publish',
            ]],
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, $product->productAttributes()->count());
        $this->assertSame($size->id, $product->productAttributes()->first()->attribute_id);

        // The surviving option is still sellable.
        $this->assertGreaterThan(0, $product->variations()->count());
    }
}
