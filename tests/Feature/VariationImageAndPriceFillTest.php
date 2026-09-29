<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\User;
use App\Services\VariationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VariationImageAndPriceFillTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    /** @return array{0: Attribute, 1: AttributeValue} */
    private function color(): array
    {
        $color = Attribute::create(['name' => 'Color', 'type' => Attribute::TYPE_COLOR]);

        return [$color, $color->values()->create(['name' => 'Pink', 'color_code' => '#ffc0cb'])];
    }

    /** @return array{0: Product, 1: ProductAttribute, 2: AttributeValue} */
    private function product(string $name): array
    {
        [$color, $pink] = $this->color();

        $product = Product::create([
            'user_id' => $this->admin()->id,
            'name' => $name,
            'slug' => strtolower($name),
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
        $productAttribute->values()->attach([$pink->id]);

        return [$product, $productAttribute, $pink];
    }

    public function test_a_variation_image_uploads_when_adding_a_product()
    {
        Storage::fake('public');
        [$color, $pink] = $this->color();

        $this->actingAs($this->admin())->post(route('admin.products.store'), [
            'name' => 'Add With Image',
            'product_type' => 'variable',
            'regular_price' => '100.00',
            'is_active' => '1',
            'attributes' => [[
                'attribute_id' => $color->id,
                'value_ids' => [$pink->id],
                'is_visible' => '1',
                'is_variation' => '1',
            ]],
            'variations' => [[
                'values' => [0 => $pink->id],
                'regular_price' => '100',
                'manage_stock' => '1',
                'stock_quantity' => 2,
                'stock_status' => 'instock',
                'status' => 'publish',
                'image' => UploadedFile::fake()->image('pink.png', 40, 40),
            ]],
        ])->assertSessionHasNoErrors();

        $variation = Product::where('name', 'Add With Image')->firstOrFail()->variations()->firstOrFail();

        $this->assertNotNull($variation->image);
        Storage::disk('public')->assertExists($variation->image);
    }

    public function test_a_variation_image_uploads_when_editing_a_product()
    {
        Storage::fake('public');
        [$product, $productAttribute, $pink] = $this->product('Edit With Image');

        app(VariationService::class)->generateVariations($product);
        $variation = $product->variations()->firstOrFail();

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'name' => 'Edit With Image',
            'product_type' => 'variable',
            'regular_price' => '100.00',
            'is_active' => '1',
            'variations' => [[
                'id' => $variation->id,
                'values' => [$productAttribute->id => $pink->id],
                'regular_price' => '100',
                'manage_stock' => '1',
                'stock_quantity' => 2,
                'stock_status' => 'instock',
                'status' => 'publish',
                'image' => UploadedFile::fake()->image('edit.png', 40, 40),
            ]],
        ])->assertSessionHasNoErrors();

        $variation->refresh();

        $this->assertNotNull($variation->image);
        Storage::disk('public')->assertExists($variation->image);
    }

    public function test_a_rejected_variation_image_is_reported_on_the_edit_page()
    {
        Storage::fake('public');
        [$product, $productAttribute, $pink] = $this->product('Too Big');

        app(VariationService::class)->generateVariations($product);
        $variation = $product->variations()->firstOrFail();

        $this->actingAs($this->admin())
            ->from(route('admin.products.edit', $product))
            ->put(route('admin.products.update', $product), [
                'name' => 'Too Big',
                'product_type' => 'variable',
                'regular_price' => '100.00',
                'is_active' => '1',
                'variations' => [[
                    'id' => $variation->id,
                    'values' => [$productAttribute->id => $pink->id],
                    'regular_price' => '100',
                    'manage_stock' => '1',
                    'stock_quantity' => 2,
                    'stock_status' => 'instock',
                    'status' => 'publish',
                    'image' => UploadedFile::fake()->create('huge.png', 3000, 'image/png'),
                ]],
            ])->assertSessionHasErrors('variations.0.image');

        // The message names the row and the field rather than the raw key.
        $this->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('Row 1')
            ->assertSee('2048 kilobytes');

        $this->assertNull($variation->fresh()->image);
    }

    public function test_a_rejected_variation_image_is_reported_on_the_create_page()
    {
        Storage::fake('public');
        [$color, $pink] = $this->color();

        $this->actingAs($this->admin())
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), [
                'name' => 'Too Big Add',
                'product_type' => 'variable',
                'regular_price' => '100.00',
                'is_active' => '1',
                'attributes' => [[
                    'attribute_id' => $color->id,
                    'value_ids' => [$pink->id],
                    'is_visible' => '1',
                    'is_variation' => '1',
                ]],
                'variations' => [[
                    'values' => [0 => $pink->id],
                    'regular_price' => '100',
                    'manage_stock' => '1',
                    'stock_quantity' => 2,
                    'stock_status' => 'instock',
                    'status' => 'publish',
                    'image' => UploadedFile::fake()->create('huge.png', 3000, 'image/png'),
                ]],
            ])->assertSessionHasErrors('variations.0.image');

        $this->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('Row 1')
            ->assertSee('2048 kilobytes');
    }

    public function test_the_add_page_offers_the_same_price_filler_as_the_edit_page()
    {
        $html = $this->actingAs($this->admin())->get(route('admin.products.create'))->getContent();

        $this->assertStringContainsString('Fill All Rows', $html);
        $this->assertStringContainsString('Fill Selected', $html);
        $this->assertStringContainsString('x-model="bulkPrice.regular"', $html);
        $this->assertStringContainsString('x-model="bulkPrice.sale"', $html);
        $this->assertStringContainsString('applyPriceToAll()', $html);
        $this->assertStringContainsString('applyPriceToSelected()', $html);

        // Unsaved rows are targeted by combination key through a per row tick.
        $this->assertStringContainsString('x-model="fillTargets"', $html);
        $this->assertStringContainsString('resolveBulkPrice()', $html);
    }

    public function test_the_price_filler_validates_like_the_server()
    {
        $script = $this->actingAs($this->admin())->get(route('admin.products.create'))->getContent();

        // A sale price at or above the regular price is refused before it can
        // reach the rows, matching the server side rule.
        $this->assertStringContainsString('The sale price must be lower than the regular price.', $script);
        $this->assertStringContainsString('A sale price needs a regular price to be lower than.', $script);
    }
}
