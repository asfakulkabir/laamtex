<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VariationStockFixTest extends TestCase
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
            'name' => 'Stock Fix Product', 'slug' => 'stock-fix-product', 'product_type' => 'variable',
            'regular_price' => '1200.00', 'is_active' => true,
        ]);

        $pa = $product->productAttributes()->create([
            'attribute_id' => $color->id, 'position' => 1,
            'is_visible' => true, 'is_variation' => true,
        ]);
        $pa->values()->attach([$pink->id]);

        return $product;
    }

    /**
     * A variation that does not manage its own stock, on a parent that also
     * does not manage stock, has no quantity limit. The product page and the
     * cart must agree.
     */
    public function test_untracked_stock_is_not_limited_to_zero()
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
                'stock_status' => 'instock',
                'status' => 'publish',
            ]],
        ])->assertSessionHasNoErrors();

        $product->refresh();
        $v = $product->variations()->first();

        $this->assertFalse($product->manage_stock);
        $this->assertFalse($v->isStockTracked());
        $this->assertTrue($v->isInStock());
        $this->assertTrue($v->isPurchasable());

        // The product page reports no limit rather than a misleading 0.
        $page = $this->get(route('product.detail', $product->slug))->assertOk();
        $json = json_decode($page->viewData('variationsJson'), true);
        $this->assertNull($json[0]['stock']);
        $this->assertTrue($json[0]['in_stock']);
        $this->assertTrue($json[0]['purchasable']);

        // And the cart accepts it.
        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'variation_id' => $v->id,
            'quantity' => 25,
        ])->assertSessionHasNoErrors();

        $this->assertCount(1, session('cart'));
    }

    /**
     * Untracking stock on a variation leaves the product sellable rather than
     * silently capping it at the parent's zero quantity.
     */
    public function test_unticking_manage_stock_keeps_the_option_sellable()
    {
        $product = $this->variableProduct();
        $admin = $this->admin();
        $pa = $product->productAttributes()->first();
        $value = $pa->values()->first();

        $base = [
            'name' => $product->name,
            'product_type' => 'variable',
            'regular_price' => '1200.00',
            'is_active' => '1',
        ];

        $this->actingAs($admin)->put(route('admin.products.update', $product), $base + [
            'variations' => [[
                'id' => null,
                'values' => [$pa->id => $value->id],
                'regular_price' => '100',
                'manage_stock' => '1',
                'stock_quantity' => 50,
                'stock_status' => 'instock',
                'status' => 'publish',
            ]],
        ])->assertSessionHasNoErrors();

        $v = $product->variations()->first();
        $this->assertTrue($v->isStockTracked());

        // Untick "Manage stock here".
        $this->actingAs($admin)->put(route('admin.products.update', $product), $base + [
            'variations' => [[
                'id' => $v->id,
                'values' => [$pa->id => $value->id],
                'regular_price' => '100',
                'stock_status' => 'instock',
                'status' => 'publish',
            ]],
        ])->assertSessionHasNoErrors();

        $v->refresh();
        $this->assertFalse($v->manage_stock);
        $this->assertFalse($v->isStockTracked());
        $this->assertTrue($v->isPurchasable());

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'variation_id' => $v->id,
            'quantity' => 1,
        ])->assertSessionHasNoErrors();
    }
}
