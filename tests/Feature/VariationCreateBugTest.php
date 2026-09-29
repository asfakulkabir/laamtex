<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VariationCreateBugTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    private function setupAttributes(): array
    {
        $color = Attribute::create(['name' => 'Color', 'type' => Attribute::TYPE_COLOR]);
        $pink = $color->values()->create(['name' => 'Pink', 'color_code' => '#ffc0cb']);

        return [$color, $pink];
    }

    public function test_create_rejects_sale_price_above_regular_price()
    {
        [$color, $pink] = $this->setupAttributes();

        $this->actingAs($this->admin())->post(route('admin.products.store'), [
            'name' => 'Create Sale Bad', 'product_type' => 'variable',
            'regular_price' => '100.00', 'is_active' => '1',
            'attributes' => [[
                'attribute_id' => $color->id, 'value_ids' => [$pink->id],
                'is_visible' => '1', 'is_variation' => '1',
            ]],
            'variations' => [[
                'values' => [0 => $pink->id],
                'regular_price' => '100', 'sale_price' => '500',
                'manage_stock' => '1', 'stock_quantity' => 3,
                'stock_status' => 'instock', 'status' => 'publish',
            ]],
        ])->assertSessionHasErrors('variations.0.sale_price');

        $this->assertSame(0, Product::count());
    }

    public function test_create_rejects_duplicate_combinations()
    {
        [$color, $pink] = $this->setupAttributes();

        $this->actingAs($this->admin())->post(route('admin.products.store'), [
            'name' => 'Create Duplicate', 'product_type' => 'variable',
            'regular_price' => '100.00', 'is_active' => '1',
            'attributes' => [[
                'attribute_id' => $color->id, 'value_ids' => [$pink->id],
                'is_visible' => '1', 'is_variation' => '1',
            ]],
            'variations' => [
                ['values' => [0 => $pink->id], 'regular_price' => '100',
                    'manage_stock' => '1', 'stock_quantity' => 1, 'stock_status' => 'instock', 'status' => 'publish'],
                ['values' => [0 => $pink->id], 'regular_price' => '200',
                    'manage_stock' => '1', 'stock_quantity' => 2, 'stock_status' => 'instock', 'status' => 'publish'],
            ],
        ])->assertSessionHasErrors('variations');

        // The half-created product is rolled back rather than left behind.
        $this->assertSame(0, Product::count());
    }

    public function test_create_accepts_a_valid_variable_product()
    {
        [$color, $pink] = $this->setupAttributes();

        $this->actingAs($this->admin())->post(route('admin.products.store'), [
            'name' => 'Create Valid', 'product_type' => 'variable',
            'regular_price' => '100.00', 'is_active' => '1',
            'attributes' => [[
                'attribute_id' => $color->id, 'value_ids' => [$pink->id],
                'is_visible' => '1', 'is_variation' => '1',
            ]],
            'variations' => [[
                'values' => [0 => $pink->id],
                'regular_price' => '100', 'sale_price' => '80',
                'manage_stock' => '1', 'stock_quantity' => 3,
                'stock_status' => 'instock', 'status' => 'publish',
            ]],
        ])->assertSessionHasNoErrors();

        $product = Product::where('name', 'Create Valid')->firstOrFail();
        $v = $product->variations()->firstOrFail();

        $this->assertEquals('80.00', $v->sale_price);
        $this->assertEquals(3, $product->stock_quantity);
    }

    /** The create page shows the duplicate combination error after a redirect. */
    public function test_create_page_shows_the_duplicate_error()
    {
        [$color, $pink] = $this->setupAttributes();

        $this->actingAs($this->admin())
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), [
                'name' => 'Create Duplicate', 'product_type' => 'variable',
                'regular_price' => '100.00', 'is_active' => '1',
                'attributes' => [[
                    'attribute_id' => $color->id, 'value_ids' => [$pink->id],
                    'is_visible' => '1', 'is_variation' => '1',
                ]],
                'variations' => [
                    ['values' => [0 => $pink->id], 'regular_price' => '100',
                        'manage_stock' => '1', 'stock_quantity' => 1, 'stock_status' => 'instock', 'status' => 'publish'],
                    ['values' => [0 => $pink->id], 'regular_price' => '200',
                        'manage_stock' => '1', 'stock_quantity' => 2, 'stock_status' => 'instock', 'status' => 'publish'],
                ],
            ])->assertSessionHasErrors('variations');

        $this->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('Variations were not saved')
            ->assertSee('Duplicate option combinations: Pink');
    }

    /** The create page shows the sale price error after a redirect. */
    public function test_create_page_shows_the_sale_price_error()
    {
        [$color, $pink] = $this->setupAttributes();

        $this->actingAs($this->admin())
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), [
                'name' => 'Create Sale Bad', 'product_type' => 'variable',
                'regular_price' => '100.00', 'is_active' => '1',
                'attributes' => [[
                    'attribute_id' => $color->id, 'value_ids' => [$pink->id],
                    'is_visible' => '1', 'is_variation' => '1',
                ]],
                'variations' => [[
                    'values' => [0 => $pink->id],
                    'regular_price' => '100', 'sale_price' => '500',
                    'manage_stock' => '1', 'stock_quantity' => 3,
                    'stock_status' => 'instock', 'status' => 'publish',
                ]],
            ])->assertSessionHasErrors('variations.0.sale_price');

        $this->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('Some sale prices were not saved');
    }
}
