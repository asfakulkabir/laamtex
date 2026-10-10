<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A freshly added variation row is seeded with a small non-zero stock count.
 * Starting at 0 rendered as "out of stock" before the admin typed anything.
 */
class VariationDefaultStockTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    public function test_add_variation_seeds_the_default_stock()
    {
        $product = Product::create([
            'name' => 'Default Stock Tee',
            'slug' => 'default-stock-tee',
            'product_type' => 'variable',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.products.edit', $product));

        $response->assertOk();
        $response->assertSee('DEFAULT_VARIATION_STOCK = 5', false);
        $response->assertSee('stock_quantity: DEFAULT_VARIATION_STOCK', false);
    }

    public function test_generated_variation_rows_start_with_stock_of_five()
    {
        $color = Attribute::create(['name' => 'Color', 'type' => Attribute::TYPE_COLOR]);
        $color->values()->create(['name' => 'Pink', 'color_code' => '#ffc0cb']);

        $response = $this->actingAs($this->admin())->get(route('admin.products.create'));
        $response->assertOk();
        $response->assertSee('DEFAULT_VARIATION_STOCK = 5', false);
        $response->assertSee('stock_quantity: DEFAULT_VARIATION_STOCK', false);
    }

    public function test_existing_variations_keep_their_stored_stock()
    {
        $product = Product::create([
            'name' => 'Existing Stock Tee',
            'slug' => 'existing-stock-tee',
            'product_type' => 'variable',
            'is_active' => true,
        ]);

        $product->variations()->create([
            'sku' => 'EXIST-1',
            'combo_key' => 'size:m',
            'status' => 'publish',
            'manage_stock' => true,
            'stock_quantity' => 42,
            'stock_status' => 'instock',
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.products.edit', $product));

        $response->assertOk();
        $response->assertSee('"stock_quantity":42', false);
    }
}