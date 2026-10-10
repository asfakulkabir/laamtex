<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductVariation;
use App\Models\User;
use App\Services\VariationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VariableProductTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    private function product(array $attributes = []): Product
    {
        static $counter = 0;
        $counter++;
        $name = $attributes['name'] ?? "Test Product {$counter}";

        return Product::create(array_merge([
            'user_id' => User::factory()->create(['role' => User::ROLE_MODERATOR])->id,
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name) . '-' . $counter,
            'product_type' => 'variable',
            'regular_price' => '100.00',
            'stock_quantity' => 0,
            'is_active' => true,
        ], $attributes));
    }

    /**
     * @return array{0: Attribute, 1: Attribute}
     */
    private function attributes(): array
    {
        $size = Attribute::create(['name' => 'Size', 'type' => Attribute::TYPE_SELECT]);
        $size->values()->createMany([
            ['name' => 'S', 'sort_order' => 1],
            ['name' => 'M', 'sort_order' => 2],
            ['name' => 'L', 'sort_order' => 3],
        ]);

        $color = Attribute::create(['name' => 'Color', 'type' => Attribute::TYPE_COLOR]);
        $color->values()->createMany([
            ['name' => 'Red', 'color_code' => '#ff0000', 'sort_order' => 1],
            ['name' => 'Blue', 'color_code' => '#0000ff', 'sort_order' => 2],
        ]);

        return [$size, $color];
    }

    // -------------------------------------------------------------------
    // Attributes
    // -------------------------------------------------------------------

    public function test_attribute_slug_is_generated_and_unique()
    {
        $first = Attribute::create(['name' => 'Material']);
        $second = Attribute::create(['name' => 'Material']);

        $this->assertSame('material', $first->slug);
        $this->assertSame('material-1', $second->slug);
    }

    public function test_color_attribute_guesses_a_color_code()
    {
        $color = Attribute::create(['name' => 'Color', 'type' => Attribute::TYPE_COLOR]);
        $value = $color->values()->create(['name' => 'Navy']);

        $this->assertSame('#000080', $value->color_code);
    }

    public function test_admin_can_manage_global_attributes_and_values()
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.attributes.index'))
            ->assertOk()
            ->assertSee('Attributes Management', false);

        $this->actingAs($admin)->post(route('admin.attributes.store'), [
            'name' => 'Material',
            'type' => 'select',
            'order_by' => 'name',
        ])->assertRedirect();

        $attribute = Attribute::firstWhere('name', 'Material');
        $this->assertNotNull($attribute);

        $this->actingAs($admin)->post(route('admin.attributes.values.store', $attribute->id), [
            'name' => 'Cotton',
        ])->assertRedirect();

        $this->assertDatabaseHas('attribute_values', [
            'attribute_id' => $attribute->id,
            'name' => 'Cotton',
        ]);

        $this->actingAs($admin)->put(route('admin.attributes.update', $attribute->id), [
            'name' => 'Fabric',
            'type' => 'select',
            'order_by' => 'name',
        ])->assertRedirect();

        $this->assertDatabaseHas('attributes', ['id' => $attribute->id, 'name' => 'Fabric']);

        $value = AttributeValue::firstWhere('name', 'Cotton');
        $this->actingAs($admin)->delete(route('admin.attributes.values.destroy', [$attribute->id, $value->id]))
            ->assertRedirect();

        $this->assertDatabaseMissing('attribute_values', ['id' => $value->id]);
    }

    public function test_attribute_in_use_by_a_product_cannot_be_deleted()
    {
        $admin = $this->admin();
        [$size] = $this->attributes();
        $product = $this->product();

        app(VariationService::class)->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => [$size->values->first()->id], 'is_variation' => 1],
        ]);

        $this->actingAs($admin)->from(route('admin.attributes.index'))
            ->delete(route('admin.attributes.destroy', $size->id))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('attributes', ['id' => $size->id]);
    }

    // -------------------------------------------------------------------
    // Attribute sync
    // -------------------------------------------------------------------

    public function test_sync_attributes_stores_selected_values()
    {
        [$size, $color] = $this->attributes();
        $product = $this->product();
        $service = app(VariationService::class);

        $service->syncAttributes($product, [
            [
                'attribute_id' => $size->id,
                'value_ids' => [$size->values[0]->id, $size->values[1]->id],
                'is_visible' => 1,
                'is_variation' => 1,
            ],
            [
                'attribute_id' => $color->id,
                'value_ids' => [$color->values[0]->id],
                'is_visible' => 0,
                'is_variation' => 1,
            ],
        ]);

        $product->load('productAttributes');

        $this->assertCount(2, $product->productAttributes);
        $this->assertSame('Size', $product->productAttributes[0]->display_name);
        $this->assertTrue($product->productAttributes[0]->is_visible);
        $this->assertFalse($product->productAttributes[1]->is_visible);
        $this->assertSame(['S', 'M'], $product->productAttributes[0]->option_names);
        $this->assertCount(2, $product->productAttributes[0]->values);
    }

    public function test_custom_attribute_creates_its_own_values()
    {
        $product = $this->product();

        app(VariationService::class)->syncAttributes($product, [
            ['custom_name' => 'Material', 'options' => 'Cotton, Silk', 'is_variation' => 1],
        ]);

        $attribute = ProductAttribute::firstWhere('product_id', $product->id);

        $this->assertTrue($attribute->isCustom());
        $this->assertSame('Material', $attribute->display_name);
        $this->assertSame(['Cotton', 'Silk'], $attribute->option_names);
        $this->assertCount(2, $attribute->values);
    }

    public function test_removing_an_attribute_row_deletes_it()
    {
        [$size, $color] = $this->attributes();
        $product = $this->product();
        $service = app(VariationService::class);

        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => [$size->values[0]->id], 'is_variation' => 1],
            ['attribute_id' => $color->id, 'value_ids' => [$color->values[0]->id], 'is_variation' => 1],
        ]);

        $this->assertSame(2, $product->productAttributes()->count());

        // Dropping one row from the set removes that attribute.
        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => [$size->values[0]->id], 'is_variation' => 1],
        ]);

        $this->assertSame(1, $product->productAttributes()->count());
        $this->assertSame($size->id, $product->productAttributes()->first()->attribute_id);
    }

    public function test_an_empty_attribute_payload_never_deletes_anything()
    {
        [$size] = $this->attributes();
        $product = $this->product();
        $service = app(VariationService::class);

        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => $size->values->pluck('id')->all(), 'is_variation' => 1],
        ]);
        $service->generateVariations($product);

        $variationCount = $product->variations()->count();
        $this->assertGreaterThan(0, $variationCount);

        // A form that sends no usable attributes must not be read as
        // "delete them all", which would cascade the variations away.
        $service->syncAttributes($product, []);
        $service->syncAttributes($product, [['attribute_id' => '', 'custom_name' => '', 'options' => '', 'value_ids' => []]]);

        $this->assertSame(1, $product->productAttributes()->count());
        $this->assertSame($variationCount, $product->variations()->count());
    }

    public function test_value_ids_from_another_attribute_are_ignored()
    {
        [$size, $color] = $this->attributes();
        $product = $this->product();

        app(VariationService::class)->syncAttributes($product, [
            [
                'attribute_id' => $size->id,
                // The red value belongs to Color, not Size.
                'value_ids' => [$color->values[0]->id, $size->values[0]->id],
                'is_variation' => 1,
            ],
        ]);

        $attribute = ProductAttribute::firstWhere('product_id', $product->id);

        $this->assertCount(1, $attribute->values);
        $this->assertSame('S', $attribute->values->first()->name);
    }

    // -------------------------------------------------------------------
    // Generating variations
    // -------------------------------------------------------------------

    public function test_generate_creates_every_combination()
    {
        [$size, $color] = $this->attributes();
        $product = $this->product();
        $service = app(VariationService::class);

        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => $size->values->pluck('id')->all(), 'is_variation' => 1],
            ['attribute_id' => $color->id, 'value_ids' => $color->values->pluck('id')->all(), 'is_variation' => 1],
        ]);

        $result = $service->generateVariations($product);

        $this->assertSame(6, $result['created']);
        $this->assertSame(0, $result['skipped']);
        $this->assertSame(6, $product->variations()->count());

        $product->variations->each(function (ProductVariation $variation) {
            $this->assertCount(2, $variation->attributeValues);
            $this->assertNotNull($variation->combo_key);
        });

        // Combination keys are unique per product.
        $keys = $product->variations->pluck('combo_key');
        $this->assertCount(6, $keys->unique());
    }

    public function test_generate_is_idempotent()
    {
        [$size, $color] = $this->attributes();
        $product = $this->product();
        $service = app(VariationService::class);

        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => $size->values->pluck('id')->all(), 'is_variation' => 1],
            ['attribute_id' => $color->id, 'value_ids' => $color->values->pluck('id')->all(), 'is_variation' => 1],
        ]);

        $service->generateVariations($product);
        $second = $service->generateVariations($product);

        $this->assertSame(0, $second['created']);
        $this->assertSame(6, $second['skipped']);
        $this->assertSame(6, $product->variations()->count());
    }

    public function test_generated_variations_get_unique_skus()
    {
        [$size, $color] = $this->attributes();
        $product = $this->product(['sku' => 'DRESS-1']);
        $service = app(VariationService::class);

        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => $size->values->pluck('id')->all(), 'is_variation' => 1],
            ['attribute_id' => $color->id, 'value_ids' => $color->values->pluck('id')->all(), 'is_variation' => 1],
        ]);
        $service->generateVariations($product);

        $skus = $product->variations->pluck('sku');

        $this->assertCount(6, $skus->unique());
        $skus->each(fn ($sku) => $this->assertStringStartsWith('DRESS-1-', $sku));
    }

    public function test_generate_does_nothing_without_variation_attributes()
    {
        $product = $this->product();

        $result = app(VariationService::class)->generateVariations($product);

        $this->assertSame(['created' => 0, 'skipped' => 0], $result);
        $this->assertSame(0, $product->variations()->count());
    }

    public function test_admin_can_generate_variations_from_the_edit_page()
    {
        $admin = $this->admin();
        [$size, $color] = $this->attributes();
        $product = $this->product();

        $this->actingAs($admin)->post(route('admin.products.generate-variations', $product), [
            'attributes' => [
                ['attribute_id' => $size->id, 'value_ids' => $size->values->pluck('id')->all(), 'is_variation' => 1],
                ['attribute_id' => $color->id, 'value_ids' => $color->values->pluck('id')->all(), 'is_variation' => 1],
            ],
        ])->assertRedirect();

        $this->assertSame(6, $product->variations()->count());
        $this->assertDatabaseHas('product_attributes', ['product_id' => $product->id, 'attribute_id' => $size->id]);
    }

    // -------------------------------------------------------------------
    // Saving variations
    // -------------------------------------------------------------------

    public function test_save_variations_stores_prices_stock_and_values()
    {
        [$size, $color] = $this->attributes();
        $product = $this->product();
        $service = app(VariationService::class);

        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => $size->values->pluck('id')->all(), 'is_variation' => 1],
            ['attribute_id' => $color->id, 'value_ids' => $color->values->pluck('id')->all(), 'is_variation' => 1],
        ]);
        $service->generateVariations($product);

        $sizeAttribute = $product->productAttributes()->where('attribute_id', $size->id)->first();
        $colorAttribute = $product->productAttributes()->where('attribute_id', $color->id)->first();

        $service->saveVariations($product, [[
            'values' => [
                $sizeAttribute->id => $size->values[0]->id,
                $colorAttribute->id => $color->values[0]->id,
            ],
            'regular_price' => '19.99',
            'sale_price' => '14.99',
            'manage_stock' => 1,
            'stock_quantity' => 7,
            'sku' => 'MY-SKU',
            'status' => 'publish',
        ]]);

        $variation = $product->variations()->first();

        $this->assertSame(1, $product->variations()->count());
        $this->assertSame('19.99', $variation->regular_price);
        $this->assertSame('14.99', $variation->sale_price);
        $this->assertSame(7, $variation->stock_quantity);
        $this->assertSame('MY-SKU', $variation->sku);
        $this->assertCount(2, $variation->attributeValues);
        $this->assertSame('S / Red', $variation->attribute_label);
    }

    public function test_sale_price_equal_or_above_regular_price_is_dropped()
    {
        [$size] = $this->attributes();
        $product = $this->product();
        $service = app(VariationService::class);

        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => [$size->values[0]->id], 'is_variation' => 1],
        ]);
        $service->generateVariations($product);

        $attribute = $product->productAttributes()->first();

        $service->saveVariations($product, [[
            'values' => [$attribute->id => $size->values[0]->id],
            'regular_price' => '20.00',
            'sale_price' => '25.00',
            'manage_stock' => 1,
            'stock_quantity' => 1,
        ]]);

        $this->assertNull($product->variations()->first()->sale_price);
    }

    public function test_duplicate_sku_is_made_unique()
    {
        [$size] = $this->attributes();
        $product = $this->product();
        $service = app(VariationService::class);

        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => $size->values->pluck('id')->all(), 'is_variation' => 1],
        ]);
        $service->generateVariations($product);

        $attribute = $product->productAttributes()->first();

        $service->saveVariations($product, [
            ['values' => [$attribute->id => $size->values[0]->id], 'regular_price' => '10', 'sku' => 'TAKEN', 'manage_stock' => 1, 'stock_quantity' => 1],
            ['values' => [$attribute->id => $size->values[1]->id], 'regular_price' => '10', 'sku' => 'TAKEN', 'manage_stock' => 1, 'stock_quantity' => 1],
        ]);

        $skus = $product->variations->pluck('sku');

        $this->assertContains('TAKEN', $skus);
        $this->assertCount(2, $skus->unique());
    }

    public function test_variation_image_is_stored_and_removable()
    {
        Storage::fake('public');

        [$size] = $this->attributes();
        $product = $this->product();
        $service = app(VariationService::class);

        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => [$size->values[0]->id], 'is_variation' => 1],
        ]);
        $service->generateVariations($product);
        $variation = $product->variations()->first();
        $attribute = $product->productAttributes()->first();

        $service->saveVariations(
            $product,
            [['values' => [$attribute->id => $size->values[0]->id], 'regular_price' => '10']],
            [0 => UploadedFile::fake()->image('red.png')]
        );

        $variation->refresh();
        Storage::disk('public')->assertExists($variation->image);
        $this->assertNotNull($variation->image_url);

        $path = $variation->image;
        $service->saveVariations($product, [[
            'id' => $variation->id,
            'values' => [$attribute->id => $size->values[0]->id],
            'regular_price' => '10',
            'remove_image' => 1,
        ]]);

        $this->assertNull($variation->fresh()->image);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_removed_variation_row_is_deleted()
    {
        [$size] = $this->attributes();
        $product = $this->product();
        $service = app(VariationService::class);

        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => $size->values->pluck('id')->all(), 'is_variation' => 1],
        ]);
        $service->generateVariations($product);

        $keep = $product->variations()->first();

        $service->saveVariations($product, [[
            'id' => $keep->id,
            'values' => [
                $product->productAttributes()->first()->id => $size->values[0]->id,
            ],
            'regular_price' => '10',
        ]]);

        $this->assertSame(1, $product->variations()->count());
        $this->assertSame($keep->id, $product->variations()->first()->id);
    }

    public function test_variation_referenced_by_an_order_is_disabled_not_deleted()
    {
        [$size] = $this->attributes();
        $product = $this->product();
        $service = app(VariationService::class);

        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => $size->values->pluck('id')->all(), 'is_variation' => 1],
        ]);
        $service->generateVariations($product);

        $variation = $product->variations()->first();
        $order = \App\Models\Order::create([
            'customer_name' => 'Test',
            'customer_phone' => '01700000000',
            'customer_address' => 'Dhaka',
            'total' => 10,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_variation_id' => $variation->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'price' => '10.00',
        ]);

        // Omitting the ordered row from the submitted set disables it rather
        // than deleting it, so the order keeps its history.
        $other = $product->variations()->skip(1)->first();

        $service->saveVariations($product, [[
            'id' => $other->id,
            'values' => [$product->productAttributes()->first()->id => $size->values[1]->id],
            'regular_price' => '10',
        ]]);

        $this->assertDatabaseHas('product_variations', [
            'id' => $variation->id,
            'status' => ProductVariation::STATUS_PRIVATE,
        ]);
    }

    public function test_an_empty_variation_payload_never_deletes_anything()
    {
        [$size] = $this->attributes();
        $product = $this->product();
        $service = app(VariationService::class);

        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => $size->values->pluck('id')->all(), 'is_variation' => 1],
        ]);
        $service->generateVariations($product);

        $variationCount = $product->variations()->count();
        $this->assertGreaterThan(0, $variationCount);

        // Rows that no longer match the product's attributes are not the same
        // as a request to delete every variation.
        $service->saveVariations($product, []);
        $service->saveVariations($product, [
            ['values' => [999999 => 999999], 'regular_price' => '10'],
        ]);

        $this->assertSame($variationCount, $product->variations()->count());
    }

    // -------------------------------------------------------------------
    // Inheritance and pricing
    // -------------------------------------------------------------------

    public function test_variation_inherits_stock_from_the_parent()
    {
        [$size] = $this->attributes();
        $product = $this->product(['stock_quantity' => 12, 'stock_status' => 'instock']);
        $service = app(VariationService::class);

        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => [$size->values[0]->id], 'is_variation' => 1],
        ]);
        $service->generateVariations($product);

        $variation = $product->variations()->first();
        $variation->update(['manage_stock' => false, 'stock_quantity' => null]);

        $this->assertSame(12, $variation->effectiveStockQuantity());
        $this->assertTrue($variation->isInStock());
    }

    public function test_variation_with_its_own_stock_ignores_the_parent()
    {
        [$size] = $this->attributes();
        $product = $this->product(['stock_quantity' => 12]);
        $service = app(VariationService::class);

        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => [$size->values[0]->id], 'is_variation' => 1],
        ]);
        $service->generateVariations($product);

        $variation = $product->variations()->first();
        $variation->update(['manage_stock' => true, 'stock_quantity' => 0, 'stock_status' => 'instock']);

        $this->assertSame(0, $variation->effectiveStockQuantity());
        $this->assertFalse($variation->isInStock());
    }

    public function test_active_price_prefers_a_lower_sale_price()
    {
        $variation = new ProductVariation(['regular_price' => '50.00', 'sale_price' => '40.00']);
        $this->assertSame(40.0, $variation->active_price);

        $variation = new ProductVariation(['regular_price' => '50.00', 'sale_price' => '60.00']);
        $this->assertSame(50.0, $variation->active_price);

        $variation = new ProductVariation(['regular_price' => '50.00']);
        $this->assertSame(50.0, $variation->active_price);

        $variation = new ProductVariation();
        $this->assertNull($variation->active_price);
    }

    public function test_disabled_variation_is_not_purchasable()
    {
        $variation = new ProductVariation(['status' => ProductVariation::STATUS_PRIVATE, 'manage_stock' => true, 'stock_quantity' => 5]);

        $this->assertFalse($variation->isEnabled());
        $this->assertFalse($variation->isPurchasable());
    }

    // -------------------------------------------------------------------
    // Published but unbuyable variations
    // -------------------------------------------------------------------

    public function test_a_published_variation_with_no_price_explains_itself()
    {
        $variation = new ProductVariation([
            'status' => ProductVariation::STATUS_PUBLISH,
            'manage_stock' => true,
            'stock_quantity' => 10,
            'regular_price' => null,
        ]);

        $this->assertTrue($variation->isEnabled());
        $this->assertTrue($variation->isInStock(), 'It has stock, only the price is missing.');
        $this->assertFalse($variation->isPurchasable());
        $this->assertSame('This option has no price yet.', $variation->unavailabilityReason());
    }

    public function test_unavailability_reason_names_the_actual_problem()
    {
        $disabled = new ProductVariation([
            'status' => ProductVariation::STATUS_PRIVATE,
            'regular_price' => '10.00',
        ]);
        $this->assertSame('This option is not available.', $disabled->unavailabilityReason());
        $this->assertSame('disabled', $disabled->unavailabilityCode());

        $outOfStock = new ProductVariation([
            'status' => ProductVariation::STATUS_PUBLISH,
            'regular_price' => '10.00',
            'manage_stock' => true,
            'stock_quantity' => 0,
            'stock_status' => ProductVariation::STOCK_OUT_OF_STOCK,
        ]);
        $this->assertSame('This option is out of stock.', $outOfStock->unavailabilityReason());
        $this->assertSame('out_of_stock', $outOfStock->unavailabilityCode());

        $fine = new ProductVariation([
            'status' => ProductVariation::STATUS_PUBLISH,
            'regular_price' => '10.00',
            'manage_stock' => true,
            'stock_quantity' => 5,
            'stock_status' => ProductVariation::STOCK_IN_STOCK,
        ]);
        $this->assertNull($fine->unavailabilityReason());
        $this->assertNull($fine->unavailabilityCode());
        $this->assertTrue($fine->isPurchasable());
    }

    public function test_product_page_marks_a_priceless_variation_as_unbuyable()
    {
        [$size] = $this->attributes();

        $product = $this->product();
        $value = $size->values()->first();
        $product->productAttributes()->create([
            'attribute_id' => $size->id,
            'position' => 1,
            'is_visible' => true,
            'is_variation' => true,
        ]);

        $variation = $product->variations()->create([
            'combo_key' => "1:{$value->id}",
            'regular_price' => null,
            'manage_stock' => true,
            'stock_quantity' => 10,
            'stock_status' => ProductVariation::STOCK_IN_STOCK,
            'status' => ProductVariation::STATUS_PUBLISH,
        ]);
        $variation->attributeValues()->create([
            'product_attribute_id' => $product->productAttributes()->first()->id,
            'attribute_value_id' => $value->id,
        ]);

        $response = $this->get(route('product.detail', $product->slug));
        $response->assertOk();

        $json = json_decode($response->viewData('variationsJson'), true);
        $this->assertCount(1, $json);
        $this->assertFalse($json[0]['purchasable']);
        $this->assertTrue($json[0]['in_stock'], 'It really is in stock.');
        $this->assertNull($json[0]['price']);
        $this->assertSame('This option has no price yet.', $json[0]['unavailable_reason']);
        $this->assertSame('no_price', $json[0]['unavailable_code']);
    }

    public function test_out_of_stock_variation_reports_a_code_so_the_page_can_translate_it()
    {
        [$size] = $this->attributes();

        $product = $this->product();
        $value = $size->values()->first();
        $product->productAttributes()->create([
            'attribute_id' => $size->id,
            'position' => 1,
            'is_visible' => true,
            'is_variation' => true,
        ]);

        $variation = $product->variations()->create([
            'combo_key' => "1:{$value->id}",
            'regular_price' => '25.00',
            'manage_stock' => true,
            'stock_quantity' => 0,
            'stock_status' => ProductVariation::STOCK_OUT_OF_STOCK,
            'status' => ProductVariation::STATUS_PUBLISH,
        ]);
        $variation->attributeValues()->create([
            'product_attribute_id' => $product->productAttributes()->first()->id,
            'attribute_value_id' => $value->id,
        ]);

        $response = $this->get(route('product.detail', $product->slug));
        $response->assertOk();

        $json = json_decode($response->viewData('variationsJson'), true);
        $this->assertFalse($json[0]['purchasable']);
        $this->assertFalse($json[0]['in_stock']);
        // An out of stock option keeps its price; only its availability changes.
        $this->assertSame(25.0, (float) $json[0]['price']);
        $this->assertSame('out_of_stock', $json[0]['unavailable_code']);
        // The product page ships the Bengali wording for the picker.
        $response->assertSee('এই অপশনটি স্টক শেষ', false);
        $response->assertDontSee('Out of Stock"', false);
    }

    public function test_adding_a_priceless_variation_to_the_cart_says_why()
    {
        $product = $this->product();
        $variation = $product->variations()->create([
            'combo_key' => '1:1',
            'regular_price' => null,
            'manage_stock' => true,
            'stock_quantity' => 10,
            'stock_status' => ProductVariation::STOCK_IN_STOCK,
            'status' => ProductVariation::STATUS_PUBLISH,
        ]);

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'variation_id' => $variation->id,
            'quantity' => 1,
        ])->assertRedirect()->assertSessionHas('error', 'This option has no price yet.');

        $this->assertSame(0, count(session('cart', [])));
    }

    public function test_admin_product_page_warns_about_priceless_variations()
    {
        [$size] = $this->attributes();
        $value = $size->values()->first();

        $product = $this->product();
        $productAttribute = $product->productAttributes()->create([
            'attribute_id' => $size->id,
            'position' => 1,
            'is_visible' => true,
            'is_variation' => true,
        ]);

        $variation = $product->variations()->create([
            'combo_key' => "1:{$value->id}",
            'regular_price' => null,
            'manage_stock' => true,
            'stock_quantity' => 4,
            'stock_status' => ProductVariation::STOCK_IN_STOCK,
            'status' => ProductVariation::STATUS_PUBLISH,
        ]);
        $variation->attributeValues()->create([
            'product_attribute_id' => $productAttribute->id,
            'attribute_value_id' => $value->id,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('option(s) cannot be bought by customers yet')
            ->assertSee('This option has no price yet.')
            ->assertSee('(published)');
    }

    public function test_admin_product_page_has_no_warning_when_every_variation_is_priced()
    {
        $product = $this->product();
        $product->variations()->create([
            'combo_key' => '1:1',
            'regular_price' => '25.00',
            'manage_stock' => true,
            'stock_quantity' => 4,
            'stock_status' => ProductVariation::STOCK_IN_STOCK,
            'status' => ProductVariation::STATUS_PUBLISH,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertDontSee('option(s) cannot be bought by customers yet');
    }

    public function test_variation_falls_back_to_the_parent_image()
    {
        $product = $this->product();
        $image = $product->images()->create([
            'image' => 'product_images/parent.png',
            'name' => 'parent',
            'is_featured' => true,
        ]);

        $variation = new ProductVariation(['product_id' => $product->id]);
        $this->assertSame('product_images/parent.png', $variation->display_image);

        $variation->image = 'product_variations/v.png';
        $this->assertSame('product_variations/v.png', $variation->display_image);
    }

    // -------------------------------------------------------------------
    // Cached price range
    // -------------------------------------------------------------------

    public function test_price_range_uses_purchasable_variations_only()
    {
        [$size] = $this->attributes();
        $product = $this->product();
        $service = app(VariationService::class);

        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => $size->values->pluck('id')->all(), 'is_variation' => 1],
        ]);
        $service->generateVariations($product);

        $product->variations()->update([
            'regular_price' => '20.00',
            'manage_stock' => 1,
            'stock_quantity' => 5,
            'stock_status' => 'instock',
            'status' => 'publish',
        ]);
        $service->refreshPriceRange($product);

        $this->assertEquals(20.0, $product->fresh()->min_price);
        $this->assertEquals(20.0, $product->fresh()->max_price);

        // Out of stock variations drop out of the range.
        $product->variations()->orderByDesc('id')->first()->update([
            'regular_price' => '99.00',
            'stock_quantity' => 0,
            'stock_status' => 'outofstock',
        ]);
        $service->refreshPriceRange($product);

        $this->assertEquals(20.0, $product->fresh()->max_price);
    }

    public function test_price_range_is_null_when_nothing_is_purchasable()
    {
        [$size] = $this->attributes();
        $product = $this->product();
        $service = app(VariationService::class);

        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => $size->values->pluck('id')->all(), 'is_variation' => 1],
        ]);
        $service->generateVariations($product);

        $product->variations()->update(['manage_stock' => 1, 'stock_quantity' => 0, 'stock_status' => 'outofstock', 'regular_price' => '20.00']);
        $service->refreshPriceRange($product);

        $this->assertNull($product->fresh()->min_price);
        $this->assertNull($product->fresh()->max_price);
    }

    public function test_variable_product_is_in_stock_when_any_variation_is()
    {
        [$size] = $this->attributes();
        $product = $this->product();
        $service = app(VariationService::class);

        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => $size->values->pluck('id')->all(), 'is_variation' => 1],
        ]);
        $service->generateVariations($product);

        $product->variations()->update(['manage_stock' => 1, 'stock_quantity' => 0, 'stock_status' => 'outofstock', 'status' => 'publish']);
        $this->assertFalse($product->fresh()->isInStock());

        $product->variations()->orderByDesc('id')->first()->update(['stock_quantity' => 3, 'stock_status' => 'instock']);
        $this->assertTrue($product->fresh()->isInStock());
    }

    public function test_product_stock_is_the_sum_of_published_variations()
    {
        [$size] = $this->attributes();
        $product = $this->product();
        $service = app(VariationService::class);

        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => $size->values->pluck('id')->all(), 'is_variation' => 1],
        ]);
        $service->generateVariations($product);

        $product->variations()->update(['manage_stock' => 1, 'stock_quantity' => 4, 'status' => 'publish']);
        $product->updateStockFromVariations();

        $this->assertSame(12, $product->fresh()->stock_quantity);
    }

    // -------------------------------------------------------------------
    // Bulk actions
    // -------------------------------------------------------------------

    public function test_bulk_price_and_stock_actions()
    {
        $admin = $this->admin();
        [$size] = $this->attributes();
        $product = $this->product();
        $service = app(VariationService::class);

        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => $size->values->pluck('id')->all(), 'is_variation' => 1],
        ]);
        $service->generateVariations($product);
        $ids = $product->variations()->pluck('id')->all();

        $this->actingAs($admin)->post(route('admin.products.variations.bulk', $product), [
            'action' => 'set_regular_price',
            'variation_ids' => $ids,
            'params' => ['value' => '30'],
        ])->assertRedirect();

        $this->assertSame(
            ['30.00'],
            $product->variations()->distinct()->pluck('regular_price')->all()
        );

        $this->actingAs($admin)->post(route('admin.products.variations.bulk', $product), [
            'action' => 'decrease_price',
            'variation_ids' => $ids,
            'params' => ['value' => '10', 'mode' => 'percent'],
        ])->assertRedirect();

        $this->assertSame(['27.00'], $product->variations()->distinct()->pluck('regular_price')->all());

        $this->actingAs($admin)->post(route('admin.products.variations.bulk', $product), [
            'action' => 'set_stock',
            'variation_ids' => $ids,
            'params' => ['value' => 0],
        ])->assertRedirect();

        $this->assertSame(0, $product->variations()->where('stock_status', 'instock')->count());

        $this->actingAs($admin)->post(route('admin.products.variations.bulk', $product), [
            'action' => 'disable',
            'variation_ids' => $ids,
        ])->assertRedirect();

        $this->assertSame(0, $product->variations()->where('status', 'publish')->count());
    }

    // -------------------------------------------------------------------
    // Admin pages
    // -------------------------------------------------------------------

    public function test_admin_product_pages_render()
    {
        $admin = $this->admin();
        [$size] = $this->attributes();
        $product = $this->product();

        app(VariationService::class)->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => $size->values->pluck('id')->all(), 'is_variation' => 1, 'is_visible' => 1],
        ]);

        $this->actingAs($admin)->get(route('admin.products.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('Product Attributes', false)
            ->assertSee('Generate from Attributes', false);
    }

    // -------------------------------------------------------------------
    // "Any value" wildcards
    // -------------------------------------------------------------------

    public function test_a_variation_can_accept_any_value_for_an_attribute()
    {
        [$size, $color] = $this->attributes();
        $product = $this->product();
        $service = app(VariationService::class);

        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => $size->values->pluck('id')->all(), 'is_variation' => 1, 'is_visible' => 1],
            ['attribute_id' => $color->id, 'value_ids' => $color->values->pluck('id')->all(), 'is_variation' => 1, 'is_visible' => 1],
        ]);

        $sizeAttribute = $product->productAttributes()->where('attribute_id', $size->id)->first();
        $colorAttribute = $product->productAttributes()->where('attribute_id', $color->id)->first();
        $small = $size->values->firstWhere('name', 'S');
        $red = $color->values->firstWhere('name', 'Red');
        $blue = $color->values->firstWhere('name', 'Blue');

        // Size is left on "— Any —", so this row covers every size.
        $service->saveVariations($product, [
            ['values' => [$sizeAttribute->id => '', $colorAttribute->id => $red->id], 'regular_price' => '25.00', 'status' => ProductVariation::STATUS_PUBLISH],
        ]);

        $variation = $product->variations()->first();
        $this->assertNotNull($variation);

        $stored = $variation->valuesByProductAttribute();
        $this->assertNull($stored[$sizeAttribute->id]->attribute_value_id, 'Size should be stored as a wildcard');
        $this->assertTrue($stored[$sizeAttribute->id]->isAny());
        $this->assertSame($red->id, $stored[$colorAttribute->id]->attribute_value_id);

        // The wildcard is part of the combination, so it gets a stable key.
        $this->assertSame(
            $service->comboKey([
                ['product_attribute_id' => $sizeAttribute->id, 'attribute_value_id' => null],
                ['product_attribute_id' => $colorAttribute->id, 'attribute_value_id' => $red->id],
            ]),
            $variation->combo_key
        );

        // A wildcard still reaches the storefront JSON as a null value so the
        // browser knows it matches any option.
        $values = [];
        foreach ($variation->valuesByProductAttribute() as $attributeId => $value) {
            $values[$attributeId] = $value->attribute_value_id;
        }

        $this->assertArrayHasKey($sizeAttribute->id, $values);
        $this->assertNull($values[$sizeAttribute->id]);
        $this->assertSame($red->id, $values[$colorAttribute->id]);
    }

    public function test_wildcard_and_specific_variations_can_coexist()
    {
        [$size, $color] = $this->attributes();
        $product = $this->product();
        $service = app(VariationService::class);

        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => $size->values->pluck('id')->all(), 'is_variation' => 1, 'is_visible' => 1],
            ['attribute_id' => $color->id, 'value_ids' => $color->values->pluck('id')->all(), 'is_variation' => 1, 'is_visible' => 1],
        ]);

        $sizeAttribute = $product->productAttributes()->where('attribute_id', $size->id)->first();
        $colorAttribute = $product->productAttributes()->where('attribute_id', $color->id)->first();
        $red = $color->values->firstWhere('name', 'Red');
        $blue = $color->values->firstWhere('name', 'Blue');

        $service->saveVariations($product, [
            ['values' => [$sizeAttribute->id => '', $colorAttribute->id => $red->id], 'regular_price' => '25.00', 'status' => ProductVariation::STATUS_PUBLISH],
            ['values' => [$sizeAttribute->id => '', $colorAttribute->id => $blue->id], 'regular_price' => '27.00', 'status' => ProductVariation::STATUS_PUBLISH],
        ]);

        $this->assertSame(2, $product->variations()->count());
        $this->assertEqualsCanonicalizing(
            [25.0, 27.0],
            $product->variations()->get()->map(fn ($v) => (float) $v->active_price)->all()
        );
    }

    // -------------------------------------------------------------------
    // Image attribute values
    // -------------------------------------------------------------------

    public function test_a_variation_without_a_price_is_not_purchasable()
    {
        $product = $this->product();
        $variation = $product->variations()->create([
            'combo_key' => '1',
            'status' => ProductVariation::STATUS_PUBLISH,
            'manage_stock' => true,
            'stock_quantity' => 5,
        ]);

        $this->assertNull($variation->active_price);
        $this->assertFalse($variation->isPurchasable());
    }

    public function test_image_attribute_value_can_be_uploaded_and_replaced()
    {
        Storage::fake('public');
        $admin = $this->admin();

        $attribute = Attribute::create(['name' => 'Pattern', 'type' => Attribute::TYPE_IMAGE]);

        $this->actingAs($admin)->post(route('admin.attributes.values.store', $attribute->id), [
            'name' => 'Striped',
            'image' => UploadedFile::fake()->image('striped.png', 40, 40),
        ])->assertRedirect();

        $value = $attribute->values()->first();
        $this->assertNotNull($value->image);
        Storage::disk('public')->assertExists($value->image);

        $original = $value->image;

        $this->actingAs($admin)->put(route('admin.attributes.values.update', [$attribute->id, $value->id]), [
            'name' => 'Striped',
            'image' => UploadedFile::fake()->image('striped-2.png', 40, 40),
        ])->assertRedirect();

        $value->refresh();
        $this->assertNotSame($original, $value->image);
        Storage::disk('public')->assertMissing($original);
        Storage::disk('public')->assertExists($value->image);

        $this->actingAs($admin)->put(route('admin.attributes.values.update', [$attribute->id, $value->id]), [
            'name' => 'Striped',
            'remove_image' => 1,
        ])->assertRedirect();

        $value->refresh();
        $this->assertNull($value->image);
        Storage::disk('public')->assertMissing($value->image === null ? $original : $value->image);
    }

    public function test_image_swatch_reaches_the_product_page_json()
    {
        Storage::fake('public');
        $product = $this->product(['name' => 'Patterned Shirt', 'slug' => 'patterned-shirt']);
        $pattern = Attribute::create(['name' => 'Pattern', 'type' => Attribute::TYPE_IMAGE]);
        $value = $pattern->values()->create(['name' => 'Striped', 'image' => 'attributes/striped.png']);
        $other = $pattern->values()->create(['name' => 'Dotted']);

        app(VariationService::class)->syncAttributes($product, [
            ['attribute_id' => $pattern->id, 'value_ids' => [$value->id, $other->id], 'is_variation' => 1, 'is_visible' => 1],
        ]);

        $response = $this->get(route('product.detail', $product->slug));
        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('background-image: url(', $content);
        $this->assertStringContainsString('attributes/striped.png', $content);
    }
}
