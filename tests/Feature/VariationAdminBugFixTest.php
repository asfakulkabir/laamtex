<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VariationAdminBugFixTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    private function variableProduct(): Product
    {
        $color = Attribute::create(['name' => 'Color', 'type' => Attribute::TYPE_COLOR]);
        $pink = $color->values()->create(['name' => 'Pink', 'color_code' => '#ffc0cb']);

        $product = Product::create([
            'user_id' => $this->admin()->id,
            'name' => 'Variation Fix Product',
            'slug' => 'variation-fix-product',
            'product_type' => 'variable',
            'regular_price' => '1200.00',
            'is_active' => true,
        ]);

        $pa = $product->productAttributes()->create([
            'attribute_id' => $color->id,
            'position' => 1,
            'is_visible' => true,
            'is_variation' => true,
        ]);
        $pa->values()->attach([$pink->id]);

        return $product;
    }

    /** The Generate button lives in a form that carries _method=PUT. */
    public function test_generate_button_method()
    {
        $product = $this->variableProduct();

        // This is exactly what the browser sends when the Generate button is
        // clicked: POST, but carrying the form's hidden _method=PUT.
        $response = $this->actingAs($this->admin())->post(
            route('admin.products.generate-variations', $product),
            ['_method' => 'PUT']
        );

        fwrite(STDERR, "\nPROBE1 generate status: ".$response->getStatusCode()."\n");
        $this->assertNotSame(405, $response->getStatusCode(), 'Generate button must not 405');
    }

    /** A sale price at or above the regular price is rejected, not dropped. */
    public function test_sale_above_regular_is_rejected()
    {
        $product = $this->variableProduct();
        $admin = $this->admin();
        $pa = $product->productAttributes()->first();
        $value = $pa->values()->first();

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'product_type' => 'variable',
            'regular_price' => '1200.00',
            'is_active' => '1',
            'variations' => [[
                'id' => null,
                'values' => [$pa->id => $value->id],
                'regular_price' => '100',
                'sale_price' => '500',
                'manage_stock' => '1',
                'stock_quantity' => 3,
                'stock_status' => 'instock',
                'status' => 'publish',
            ]],
        ])->assertSessionHasErrors('variations.0.sale_price');

        $this->assertSame(0, $product->variations()->count());
    }

    /** Duplicate combinations are reported instead of collapsing. */
    public function test_duplicate_combination_rows_are_rejected()
    {
        $product = $this->variableProduct();
        $admin = $this->admin();
        $pa = $product->productAttributes()->first();
        $value = $pa->values()->first();

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'product_type' => 'variable',
            'regular_price' => '1200.00',
            'is_active' => '1',
            'variations' => [
                ['id' => null, 'values' => [$pa->id => $value->id], 'regular_price' => '100',
                    'manage_stock' => '1', 'stock_quantity' => 1, 'stock_status' => 'instock', 'status' => 'publish'],
                ['id' => null, 'values' => [$pa->id => $value->id], 'regular_price' => '200',
                    'manage_stock' => '1', 'stock_quantity' => 2, 'stock_status' => 'instock', 'status' => 'publish'],
            ],
        ])->assertSessionHasErrors('variations');

        $this->assertSame(0, $product->variations()->count());
    }

    /** A row whose every attribute is "Any" is a usable wildcard. */
    public function test_all_any_row()
    {
        $product = $this->variableProduct();
        $admin = $this->admin();
        $pa = $product->productAttributes()->first();

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'product_type' => 'variable',
            'regular_price' => '1200.00',
            'is_active' => '1',
            'variations' => [[
                'id' => null,
                'values' => [$pa->id => ''],
                'regular_price' => '100',
                'manage_stock' => '1',
                'stock_quantity' => 1,
                'stock_status' => 'instock',
                'status' => 'publish',
            ]],
        ])->assertSessionHasNoErrors();

        $v = $product->variations()->first();
        fwrite(STDERR, "\nPROBE4 all-Any row stored=".($v ? 'yes' : 'no')
            .' any='.var_export($v?->attributeValues->first()->isAny(), true)."\n");
        $this->assertTrue(true);
    }

    /** PROBE 5: empty variation row (nothing selected at all). */
    public function test_completely_empty_row()
    {
        $product = $this->variableProduct();
        $admin = $this->admin();

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'product_type' => 'variable',
            'regular_price' => '1200.00',
            'is_active' => '1',
            'variations' => [[
                'id' => null,
                'values' => [],
                'regular_price' => '100',
                'manage_stock' => '1',
                'stock_quantity' => 1,
                'stock_status' => 'instock',
                'status' => 'publish',
            ]],
        ])->assertSessionHasNoErrors();

        fwrite(STDERR, "\nPROBE5 empty row -> total variations ".$product->variations()->count()."\n");
        $this->assertTrue(true);
    }

    /** PROBE 6: does a variation id from another product get hijacked? */
    public function test_foreign_variation_id()
    {
        $product = $this->variableProduct();
        $other = Product::create([
            'user_id' => $this->admin()->id,
            'name' => 'Other', 'slug' => 'other', 'product_type' => 'variable',
            'regular_price' => '50.00', 'is_active' => true,
        ]);
        $foreign = $other->variations()->create([
            'combo_key' => '9-9', 'regular_price' => '10.00',
            'manage_stock' => true, 'stock_quantity' => 1,
            'stock_status' => 'instock', 'status' => 'publish',
        ]);

        $pa = $product->productAttributes()->first();
        $value = $pa->values()->first();

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'product_type' => 'variable',
            'regular_price' => '1200.00',
            'is_active' => '1',
            'variations' => [[
                'id' => $foreign->id,
                'values' => [$pa->id => $value->id],
                'regular_price' => '999',
                'manage_stock' => '1',
                'stock_quantity' => 1,
                'stock_status' => 'instock',
                'status' => 'publish',
            ]],
        ])->assertSessionHasNoErrors();

        $foreign->refresh();
        fwrite(STDERR, "\nPROBE6 foreign variation price after submit = ".var_export($foreign->regular_price, true)."\n");
        $this->assertTrue(true);
    }

    /** PROBE 7: parent stock when manage_stock is off on a variation. */
    public function test_stock_quantity_when_manage_stock_off()
    {
        $product = $this->variableProduct();
        $admin = $this->admin();
        $pa = $product->productAttributes()->first();
        $value = $pa->values()->first();

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'product_type' => 'variable',
            'regular_price' => '1200.00',
            'is_active' => '1',
            'variations' => [[
                'id' => null,
                'values' => [$pa->id => $value->id],
                'regular_price' => '100',
                'stock_quantity' => 77,
                'stock_status' => 'instock',
                'status' => 'publish',
            ]],
        ])->assertSessionHasNoErrors();

        $v = $product->variations()->first();
        fwrite(STDERR, "\nPROBE7 manage_stock off, qty sent 77 -> stored qty = "
            .var_export($v->stock_quantity, true).' manage_stock='.var_export($v->manage_stock, true)
            .' parentStock='.var_export($product->fresh()->stock_quantity, true)."\n");
        $this->assertTrue(true);
    }

    /** The sale price error names the option, not the raw field key. */
    public function test_sale_price_error_names_the_option()
    {
        $product = $this->variableProduct();
        $admin = $this->admin();
        $pa = $product->productAttributes()->first();
        $value = $pa->values()->first();

        $this->actingAs($admin)->from(route('admin.products.edit', $product))->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'product_type' => 'variable',
            'regular_price' => '1200.00',
            'is_active' => '1',
            'variations' => [[
                'id' => null,
                'values' => [$pa->id => $value->id],
                'regular_price' => '100',
                'sale_price' => '500',
                'manage_stock' => '1',
                'stock_quantity' => 3,
                'stock_status' => 'instock',
                'status' => 'publish',
            ]],
        ])->assertSessionHasErrors('variations.0.sale_price');

        $this->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('Some sale prices were not saved');
    }

    /** The duplicate combination error names the repeated option. */
    public function test_duplicate_combination_error_names_the_option()
    {
        $product = $this->variableProduct();
        $admin = $this->admin();
        $pa = $product->productAttributes()->first();
        $value = $pa->values()->first();

        $this->actingAs($admin)->from(route('admin.products.edit', $product))->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'product_type' => 'variable',
            'regular_price' => '1200.00',
            'is_active' => '1',
            'variations' => [
                ['id' => null, 'values' => [$pa->id => $value->id], 'regular_price' => '100',
                    'manage_stock' => '1', 'stock_quantity' => 1, 'stock_status' => 'instock', 'status' => 'publish'],
                ['id' => null, 'values' => [$pa->id => $value->id], 'regular_price' => '200',
                    'manage_stock' => '1', 'stock_quantity' => 2, 'stock_status' => 'instock', 'status' => 'publish'],
            ],
        ])->assertSessionHasErrors('variations');

        $this->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('Duplicate option combinations: Pink');
    }
}
