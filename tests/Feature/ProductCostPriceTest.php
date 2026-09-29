<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\DeliveryCharge;
use App\Models\Product;
use App\Models\User;
use App\Services\VariationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCostPriceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    private function product(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'user_id' => $this->admin()->id,
            'name' => 'Costed Product',
            'slug' => 'costed-product',
            'product_type' => 'simple',
            'regular_price' => '100.00',
            'stock_quantity' => 5,
            'is_active' => true,
        ], $attributes));
    }

    // -------------------------------------------------------------------
    // Storage
    // -------------------------------------------------------------------

    public function test_cost_price_is_stored_on_creation()
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Kurta',
            'product_type' => 'simple',
            'regular_price' => '1200',
            'cost_price' => '640.50',
            'stock_quantity' => 4,
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::firstWhere('name', 'Kurta');

        $this->assertSame('640.50', $product->cost_price);
    }

    public function test_cost_price_can_be_updated()
    {
        $admin = $this->admin();
        $product = $this->product();

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'product_type' => 'simple',
            'regular_price' => '100',
            'cost_price' => '35.25',
            'stock_quantity' => 5,
        ])->assertRedirect();

        $this->assertSame('35.25', $product->fresh()->cost_price);
    }

    public function test_a_blank_cost_price_stores_null_not_zero()
    {
        $admin = $this->admin();
        $product = $this->product(['cost_price' => '50.00']);

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'product_type' => 'simple',
            'regular_price' => '100',
            'cost_price' => '',
            'stock_quantity' => 5,
        ])->assertRedirect();

        $this->assertNull($product->fresh()->cost_price);
    }

    public function test_a_negative_cost_price_is_rejected()
    {
        $admin = $this->admin();
        $product = $this->product();

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'product_type' => 'simple',
            'regular_price' => '100',
            'cost_price' => '-10',
            'stock_quantity' => 5,
        ])->assertSessionHasErrors('cost_price');

        $this->assertNull($product->fresh()->cost_price);
    }

    // -------------------------------------------------------------------
    // Confidential: must never reach the storefront
    // -------------------------------------------------------------------

    public function test_cost_price_is_hidden_from_array_and_json_serialisation()
    {
        $product = $this->product(['cost_price' => '42.00']);

        $this->assertArrayNotHasKey('cost_price', $product->toArray());
        $this->assertArrayNotHasKey('cost_price', $product->fresh()->toArray());
        $this->assertStringNotContainsString('cost_price', $product->toJson());
        $this->assertStringNotContainsString('42.00', $product->toJson());

        // The attribute itself stays readable for admin code.
        $this->assertSame('42.00', $product->cost_price);
    }

    public function test_storefront_pages_never_expose_the_cost_price()
    {
        $size = Attribute::create(['name' => 'Size', 'type' => Attribute::TYPE_SELECT]);
        $size->values()->createMany([['name' => 'S', 'sort_order' => 1], ['name' => 'M', 'sort_order' => 2]]);

        $simple = $this->product(['cost_price' => '41.00']);

        $variable = $this->product([
            'name' => 'Costed Shirt',
            'slug' => 'costed-shirt',
            'product_type' => 'variable',
            'cost_price' => '59.99',
        ]);

        app(VariationService::class)->syncAttributes($variable, [
            ['attribute_id' => $size->id, 'value_ids' => $size->values->pluck('id')->all(), 'is_variation' => 1, 'is_visible' => 1],
        ]);
        app(VariationService::class)->generateVariations($variable);
        $variable->variations()->update([
            'regular_price' => '100', 'manage_stock' => 1, 'stock_quantity' => 5, 'status' => 'publish',
        ]);
        $variable->updateStockFromVariations();

        $pages = [
            'home' => route('home'),
            'shop' => route('shop'),
            'product detail' => route('product.detail', $variable->slug),
        ];

        foreach ($pages as $label => $url) {
            $body = $this->get($url)->assertOk()->getContent();

            $this->assertStringNotContainsString('cost_price', $body, "{$label} leaked the column name");
            $this->assertStringNotContainsString('41.00', $body, "{$label} leaked the simple product cost");
            $this->assertStringNotContainsString('59.99', $body, "{$label} leaked the variable product cost");
        }
    }

    public function test_cart_and_checkout_never_expose_the_cost_price()
    {
        $product = $this->product([
            'name' => 'Cart Cost Product',
            'slug' => 'cart-cost-product',
            'regular_price' => '100',
            'sale_price' => '90',
            'manage_stock' => true,
            'stock_quantity' => 10,
            'stock_status' => 'instock',
            'cost_price' => '33.00',
        ]);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);

        foreach (['cart' => route('cart'), 'checkout' => route('checkout')] as $label => $url) {
            $body = $this->get($url)->getContent();

            $this->assertStringNotContainsString('cost_price', $body, "{$label} leaked the column name");
            $this->assertStringNotContainsString('33.00', $body, "{$label} leaked the cost");
        }
    }

    public function test_order_records_do_not_copy_the_cost_price()
    {
        $zone = DeliveryCharge::create(['zone' => 'Dhaka', 'charge' => 50, 'is_active' => true]);

        $product = $this->product([
            'name' => 'Ordered Product',
            'slug' => 'ordered-product',
            'regular_price' => '100',
            'manage_stock' => true,
            'stock_quantity' => 10,
            'stock_status' => 'instock',
            'cost_price' => '27.00',
        ]);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 2]);

        $this->post(route('checkout.place'), [
            'customer_name' => 'Buyer',
            'customer_phone' => '01700000000',
            'customer_address' => 'Dhaka',
            'delivery_zone' => $zone->zone,
            'payment_method' => 'cod',
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', ['customer_phone' => '01700000000']);

        // 2 x 100 + 50 delivery. The 27.00 cost must not surface anywhere.
        $this->assertDatabaseHas('orders', ['total' => 250]);

        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('order_items');
        $this->assertNotContains('cost_price', $columns, 'order_items must not snapshot the cost');
    }

    // -------------------------------------------------------------------
    // Admin surface
    // -------------------------------------------------------------------

    public function test_admin_forms_expose_the_cost_price()
    {
        $admin = $this->admin();
        $product = $this->product(['cost_price' => '44.00']);

        $create = $this->actingAs($admin)->get(route('admin.products.create'))->assertOk()->getContent();
        $this->assertStringContainsString('name="cost_price"', $create);
        $this->assertStringContainsString('Admin only', $create);

        $edit = $this->actingAs($admin)->get(route('admin.products.edit', $product))->assertOk()->getContent();
        $this->assertStringContainsString('name="cost_price"', $edit);
        $this->assertStringContainsString('44.00', $edit);
        $this->assertStringContainsString('Admin only', $edit);
    }

    public function test_a_negative_cost_is_clamped_when_written_directly()
    {
        $product = $this->product();

        $product->forceFill(['cost_price' => -5])->save();

        $this->assertSame('0.00', $product->fresh()->cost_price);
    }
}
