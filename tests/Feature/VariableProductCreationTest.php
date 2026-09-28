<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\User;
use App\Services\VariationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Covers the WooCommerce authoring path: create the product as variable, pick
 * attributes and their values, then set price/image per row and see it on the
 * detail page.
 */
class VariableProductCreationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    /**
     * @return array{0: Attribute, 1: AttributeValue, 2: AttributeValue, 3: Attribute, 4: AttributeValue, 5: AttributeValue}
     */
    private function attributes(): array
    {
        $size = Attribute::create(['name' => 'Size', 'type' => Attribute::TYPE_SELECT]);
        $small = $size->values()->create(['name' => 'S']);
        $medium = $size->values()->create(['name' => 'M']);

        $color = Attribute::create(['name' => 'Color', 'type' => Attribute::TYPE_COLOR]);
        $red = $color->values()->create(['name' => 'Red', 'color_code' => '#ff0000']);
        $blue = $color->values()->create(['name' => 'Blue', 'color_code' => '#0000ff']);

        return [$size, $small, $medium, $color, $red, $blue];
    }

    public function test_creating_a_variable_product_generates_every_combination()
    {
        $admin = $this->admin();
        [$size, $small, $medium, $color, $red, $blue] = $this->attributes();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Flow Dress',
            'slug' => 'flow-dress',
            'product_type' => 'variable',
            'regular_price' => '50',
            'stock_quantity' => 0,
            'is_active' => 1,
            'short_description' => '',
            'description' => '',
            'categories' => [],
            'attributes' => [
                ['attribute_id' => $size->id, 'value_ids' => [$small->id, $medium->id], 'is_visible' => 1, 'is_variation' => 1],
                ['attribute_id' => $color->id, 'value_ids' => [$red->id, $blue->id], 'is_visible' => 1, 'is_variation' => 1],
            ],
        ])->assertRedirect();

        $product = Product::firstWhere('slug', 'flow-dress');
        $this->assertNotNull($product);
        $this->assertSame(2, $product->productAttributes()->count());

        // 2 sizes x 2 colors, created without a second "generate" step.
        $this->assertSame(4, $product->variations()->count());
    }

    public function test_prices_images_and_swaps_reach_the_detail_page()
    {
        $admin = $this->admin();
        [$size, $small, $medium, $color, $red, $blue] = $this->attributes();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Flow Dress',
            'slug' => 'flow-dress',
            'product_type' => 'variable',
            'regular_price' => '50',
            'stock_quantity' => 0,
            'is_active' => 1,
            'short_description' => '',
            'description' => '',
            'categories' => [],
            'attributes' => [
                ['attribute_id' => $size->id, 'value_ids' => [$small->id, $medium->id], 'is_visible' => 1, 'is_variation' => 1],
                ['attribute_id' => $color->id, 'value_ids' => [$red->id, $blue->id], 'is_visible' => 1, 'is_variation' => 1],
            ],
        ])->assertRedirect();

        $product = Product::firstWhere('slug', 'flow-dress');
        $productAttributes = $product->productAttributes()->pluck('id', 'attribute_id');
        $sizeAttribute = $productAttributes[$size->id];
        $colorAttribute = $productAttributes[$color->id];

        $service = app(VariationService::class);
        $key = fn ($sizeValue, $colorValue) => $service->comboKey([
            ['product_attribute_id' => $sizeAttribute, 'attribute_value_id' => $sizeValue],
            ['product_attribute_id' => $colorAttribute, 'attribute_value_id' => $colorValue],
        ]);

        $redSmall = $product->variations()->firstWhere('combo_key', $key($small->id, $red->id));
        $blueMedium = $product->variations()->firstWhere('combo_key', $key($medium->id, $blue->id));
        $this->assertNotNull($redSmall);
        $this->assertNotNull($blueMedium);

        Storage::fake('public');
        $service->saveVariations($product, [
            [
                'id' => $redSmall->id,
                'values' => [$sizeAttribute => $small->id, $colorAttribute => $red->id],
                'regular_price' => '30.00', 'manage_stock' => 1, 'stock_quantity' => 4,
                'status' => ProductVariation::STATUS_PUBLISH,
            ],
            [
                'id' => $blueMedium->id,
                'values' => [$sizeAttribute => $medium->id, $colorAttribute => $blue->id],
                'regular_price' => '45.00', 'manage_stock' => 1, 'stock_quantity' => 2,
                'status' => ProductVariation::STATUS_PUBLISH,
            ],
        ], [
            0 => UploadedFile::fake()->image('red-small.png', 60, 60),
        ]);

        // The parent range comes from the priced, purchasable rows only.
        $product->refresh();
        $this->assertEquals(30.0, (float) $product->min_price);
        $this->assertEquals(45.0, (float) $product->max_price);
        $this->assertNotNull($redSmall->refresh()->image);

        $html = $this->get(route('product.detail', 'flow-dress'))->assertOk()->getContent();

        $this->assertStringContainsString('Size', $html);
        $this->assertStringContainsString('Color', $html);
        $this->assertStringContainsString('#ff0000', $html, 'color swatch should render');
        $this->assertStringContainsString('30', $html);
        $this->assertStringContainsString('45', $html);
        $this->assertStringContainsString(
            'product_variations',
            $html,
            'the variation image must ship to the storefront so the main image can swap on selection'
        );
    }

    public function test_adding_a_term_adds_only_the_missing_rows_and_keeps_prices()
    {
        $admin = $this->admin();
        $size = Attribute::create(['name' => 'Size', 'type' => Attribute::TYPE_SELECT]);
        $small = $size->values()->create(['name' => 'S']);
        $medium = $size->values()->create(['name' => 'M']);

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Grow Tee',
            'slug' => 'grow-tee',
            'product_type' => 'variable',
            'regular_price' => '20',
            'stock_quantity' => 0,
            'is_active' => 1,
            'short_description' => '',
            'description' => '',
            'categories' => [],
            'attributes' => [
                ['attribute_id' => $size->id, 'value_ids' => [$small->id], 'is_visible' => 1, 'is_variation' => 1],
            ],
        ])->assertRedirect();

        $product = Product::firstWhere('slug', 'grow-tee');
        $this->assertSame(1, $product->variations()->count());

        // Price the existing row; regeneration must not reset it.
        $product->variations()->first()->update([
            'regular_price' => '19.00',
            'status' => ProductVariation::STATUS_PUBLISH,
        ]);

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => 'Grow Tee',
            'slug' => 'grow-tee',
            'product_type' => 'variable',
            'regular_price' => '20',
            'stock_quantity' => 0,
            'is_active' => 1,
            'short_description' => '',
            'description' => '',
            'categories' => [],
            'attributes' => [
                ['attribute_id' => $size->id, 'value_ids' => [$small->id, $medium->id], 'is_visible' => 1, 'is_variation' => 1],
            ],
        ])->assertRedirect();

        $this->assertSame(2, $product->variations()->count(), 'the new term should add one row');
        $this->assertSame(
            1,
            $product->variations()->where('regular_price', 19.00)->count(),
            'the already priced row must survive regeneration'
        );
    }

    /**
     * The create screen renders the variation rows itself and posts them with
     * the product, keyed by the attribute's position because the attributes do
     * not have database ids yet.
     */
    public function test_create_screen_saves_inline_variations_in_one_step()
    {
        $admin = $this->admin();
        [$size, $small, $medium, $color, $red] = $this->attributes();

        Storage::fake('public');

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Inline Dress',
            'slug' => 'inline-dress',
            'product_type' => 'variable',
            'regular_price' => '99',
            'stock_quantity' => 0,
            'is_active' => 1,
            'short_description' => '',
            'description' => '',
            'categories' => [],
            'attributes' => [
                ['attribute_id' => $size->id, 'value_ids' => [$small->id, $medium->id], 'is_visible' => 1, 'is_variation' => 1],
                ['attribute_id' => $color->id, 'value_ids' => [$red->id], 'is_visible' => 1, 'is_variation' => 1],
            ],
            // Keys are the attribute positions, as the Alpine component sends them.
            'variations' => [
                [
                    'id' => '',
                    'combo_key' => 'a1:' . $small->id . '|a2:' . $red->id,
                    'values' => ['0' => $small->id, '1' => $red->id],
                    'regular_price' => '30.00', 'sale_price' => '', 'manage_stock' => 1,
                    'stock_quantity' => 4, 'stock_status' => 'instock', 'sku' => '',
                    'weight_value' => '', 'status' => ProductVariation::STATUS_PUBLISH,
                    'image' => UploadedFile::fake()->image('red-small.png', 50, 50),
                ],
                [
                    'id' => '',
                    'combo_key' => 'a1:' . $medium->id . '|a2:' . $red->id,
                    'values' => ['0' => $medium->id, '1' => $red->id],
                    'regular_price' => '35.00', 'sale_price' => '', 'manage_stock' => 1,
                    'stock_quantity' => 2, 'stock_status' => 'instock', 'sku' => '',
                    'weight_value' => '', 'status' => ProductVariation::STATUS_PUBLISH,
                ],
            ],
        ])->assertRedirect();

        $product = Product::firstWhere('slug', 'inline-dress');
        $this->assertNotNull($product);
        $this->assertSame(2, $product->variations()->count());

        $this->assertEqualsCanonicalizing(
            [30.0, 35.0],
            $product->variations()->get()->map(fn ($v) => (float) $v->regular_price)->all()
        );
        $this->assertEquals(30.0, (float) $product->min_price);
        $this->assertEquals(35.0, (float) $product->max_price);

        $redSmall = $product->variations()->where('regular_price', 30.0)->first();
        $this->assertNotNull($redSmall->image, 'the inline image should be stored');

        // The positions were resolved to the real attribute/value rows.
        $stored = $redSmall->valuesByProductAttribute();
        $this->assertCount(2, $stored, 'both attributes should be linked to the variation');
        $this->assertContains($small->id, $stored->map(fn ($v) => $v->attribute_value_id)->all());
        $this->assertContains($red->id, $stored->map(fn ($v) => $v->attribute_value_id)->all());
    }

    public function test_create_screen_saves_custom_attribute_variations_keyed_by_name()
    {
        $admin = $this->admin();

        // A custom attribute has no value rows until the server creates them, so
        // the form identifies the options by name.
        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Cotton Tee',
            'slug' => 'cotton-tee',
            'product_type' => 'variable',
            'regular_price' => '99',
            'stock_quantity' => 0,
            'is_active' => 1,
            'short_description' => '',
            'description' => '',
            'categories' => [],
            'attributes' => [
                ['attribute_id' => '', 'custom_name' => 'Material', 'options' => 'Cotton, Silk', 'is_visible' => 1, 'is_variation' => 1],
            ],
            'variations' => [
                ['id' => '', 'combo_key' => 'a1:Cotton', 'values' => ['0' => 'Cotton'],
                    'regular_price' => '18.00', 'manage_stock' => 1, 'stock_quantity' => 5,
                    'stock_status' => 'instock', 'sku' => '', 'weight_value' => '', 'status' => ProductVariation::STATUS_PUBLISH],
                ['id' => '', 'combo_key' => 'a1:Silk', 'values' => ['0' => 'Silk'],
                    'regular_price' => '22.00', 'manage_stock' => 1, 'stock_quantity' => 3,
                    'stock_status' => 'instock', 'sku' => '', 'weight_value' => '', 'status' => ProductVariation::STATUS_PUBLISH],
            ],
        ])->assertRedirect();

        $product = Product::firstWhere('slug', 'cotton-tee');
        $this->assertSame(2, $product->variations()->count());

        // Each variation is linked to the loose value row created for its option.
        $byPrice = $product->variations()->get()->keyBy(fn ($v) => (float) $v->regular_price);

        $this->assertSame(
            'Cotton',
            $byPrice[18.0]->attributeValues->first()->attributeValue->name
        );
        $this->assertSame(
            'Silk',
            $byPrice[22.0]->attributeValues->first()->attributeValue->name
        );
        $this->assertEquals(18.0, (float) $product->min_price);
        $this->assertEquals(22.0, (float) $product->max_price);
    }

    public function test_create_screen_can_disable_a_single_variation_row()
    {
        $admin = $this->admin();
        [$size, $small, $medium] = $this->attributes();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Partly Dress',
            'slug' => 'partly-dress',
            'product_type' => 'variable',
            'regular_price' => '99',
            'stock_quantity' => 0,
            'is_active' => 1,
            'short_description' => '',
            'description' => '',
            'categories' => [],
            'attributes' => [
                ['attribute_id' => $size->id, 'value_ids' => [$small->id, $medium->id], 'is_visible' => 1, 'is_variation' => 1],
            ],
            'variations' => [
                ['id' => '', 'combo_key' => 'a1:' . $small->id, 'values' => ['0' => $small->id],
                    'regular_price' => '30.00', 'manage_stock' => 1, 'stock_quantity' => 4,
                    'stock_status' => 'instock', 'sku' => '', 'weight_value' => '',
                    'status' => ProductVariation::STATUS_PUBLISH],
                ['id' => '', 'combo_key' => 'a1:' . $medium->id, 'values' => ['0' => $medium->id],
                    'regular_price' => '35.00', 'manage_stock' => 1, 'stock_quantity' => 2,
                    'stock_status' => 'instock', 'sku' => '', 'weight_value' => '',
                    'status' => ProductVariation::STATUS_PRIVATE],
            ],
        ])->assertRedirect();

        $product = Product::firstWhere('slug', 'partly-dress');

        $enabled = $product->variations()->where('regular_price', 30.0)->first();
        $disabled = $product->variations()->where('regular_price', 35.0)->first();

        $this->assertSame(ProductVariation::STATUS_PUBLISH, $enabled->status);
        $this->assertSame(ProductVariation::STATUS_PRIVATE, $disabled->status);

        // Only purchasable rows feed the parent range.
        $this->assertTrue($enabled->isPurchasable());
        $this->assertFalse($disabled->isPurchasable());
        $this->assertEquals(30.0, (float) $product->min_price);
        $this->assertEquals(30.0, (float) $product->max_price);
    }
}
