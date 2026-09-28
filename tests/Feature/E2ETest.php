<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\DeliveryCharge;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class E2ETest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $simpleProduct;
    protected $variableProduct;
    protected $variationS;
    protected $variationM;
    protected $deliveryZone;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Admin
        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@outfitt.com',
            'password' => Hash::make('adminpassword'),
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        // 2. Create Categories
        $catWomen = Category::create(['name' => 'Women', 'slug' => 'women']);
        $catDresses = Category::create(['name' => 'Dresses', 'parent_id' => $catWomen->id, 'slug' => 'dresses']);

        // 3. Create Delivery Charge
        $this->deliveryZone = DeliveryCharge::create([
            'zone' => 'Inside Dhaka',
            'charge' => 60.00,
            'estimated_days' => '2-3 Days',
        ]);

        // 4. Create Simple Product
        $this->simpleProduct = Product::create([
            'user_id' => $this->adminUser->id,
            'name' => 'Purple Leather Handbag',
            'slug' => 'purple-leather-handbag',
            'short_description' => 'A luxury leather handbag.',
            'product_type' => 'simple',
            'regular_price' => 50.00,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);
        $this->simpleProduct->categories()->sync([$catWomen->id]);

        // 5. Create Variable Product
        $this->variableProduct = Product::create([
            'user_id' => $this->adminUser->id,
            'name' => 'Casual Summer Dress',
            'slug' => 'casual-summer-dress',
            'short_description' => 'A casual dress.',
            'product_type' => 'variable',
            'regular_price' => 40.00,
            'is_active' => true,
        ]);
        $this->variableProduct->categories()->sync([$catDresses->id]);

        // Build it the way the admin does: attributes, then variations.
        $size = Attribute::create(['name' => 'Size', 'type' => Attribute::TYPE_SELECT]);
        $sizeSmall = $size->values()->create(['name' => 'S', 'sort_order' => 1]);
        $sizeMedium = $size->values()->create(['name' => 'M', 'sort_order' => 2]);

        $color = Attribute::create(['name' => 'Color', 'type' => Attribute::TYPE_COLOR]);
        $colorPink = $color->values()->create(['name' => 'Pink', 'color_code' => '#ffc0cb', 'sort_order' => 1]);

        $variationService = app(\App\Services\VariationService::class);
        $variationService->syncAttributes($this->variableProduct, [
            ['attribute_id' => $size->id, 'value_ids' => [$sizeSmall->id, $sizeMedium->id], 'is_visible' => 1, 'is_variation' => 1],
            ['attribute_id' => $color->id, 'value_ids' => [$colorPink->id], 'is_visible' => 1, 'is_variation' => 1],
        ]);
        $variationService->generateVariations($this->variableProduct);

        $sizeAttribute = $this->variableProduct->productAttributes()->where('attribute_id', $size->id)->first();
        $colorAttribute = $this->variableProduct->productAttributes()->where('attribute_id', $color->id)->first();

        $variationService->saveVariations($this->variableProduct, [
            [
                'values' => [$sizeAttribute->id => $sizeSmall->id, $colorAttribute->id => $colorPink->id],
                'regular_price' => 30.00,
                'manage_stock' => 1,
                'stock_quantity' => 5,
                'status' => ProductVariation::STATUS_PUBLISH,
            ],
            [
                'values' => [$sizeAttribute->id => $sizeMedium->id, $colorAttribute->id => $colorPink->id],
                'regular_price' => 35.00,
                'manage_stock' => 1,
                'stock_quantity' => 8,
                'status' => ProductVariation::STATUS_PUBLISH,
            ],
        ]);

        $this->variationS = $this->variableProduct->variations()
            ->where('combo_key', $variationService->comboKey([
                ['product_attribute_id' => $sizeAttribute->id, 'attribute_value_id' => $sizeSmall->id],
                ['product_attribute_id' => $colorAttribute->id, 'attribute_value_id' => $colorPink->id],
            ]))
            ->firstOrFail();

        $this->variationM = $this->variableProduct->variations()
            ->where('combo_key', $variationService->comboKey([
                ['product_attribute_id' => $sizeAttribute->id, 'attribute_value_id' => $sizeMedium->id],
                ['product_attribute_id' => $colorAttribute->id, 'attribute_value_id' => $colorPink->id],
            ]))
            ->firstOrFail();

        $variationService->refreshPriceRange($this->variableProduct);
        $this->variableProduct->updateStockFromVariations();
    }

    public function test_entire_ecommerce_and_admin_workflow(): void
    {
        // 1. Visit Homepage
        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee('Purple Leather Handbag');

        // 2. Visit Shop Page
        $response = $this->get(route('shop'));
        $response->assertStatus(200);
        $response->assertSee('Purple Leather Handbag');
        $response->assertSee('Casual Summer Dress');

        // 3. Visit Product Details Page
        $response = $this->get(route('product.detail', $this->simpleProduct->slug));
        $response->assertStatus(200);
        $response->assertSee('Purple Leather Handbag');

        $response = $this->get(route('product.detail', $this->variableProduct->slug));
        $response->assertStatus(200);
        $response->assertSee('Casual Summer Dress');

        // 4. Add Simple Product to Cart
        $response = $this->post(route('cart.add'), [
            'product_id' => $this->simpleProduct->id,
            'quantity' => 2,
        ]);
        $response->assertRedirect(route('cart'));

        $this->assertEquals(1, count(session('cart')));

        // 5. Add Variable Product (Size S) to Cart
        $response = $this->post(route('cart.add'), [
            'product_id' => $this->variableProduct->id,
            'quantity' => 1,
            'variation_id' => $this->variationS->id,
        ]);
        $response->assertRedirect(route('cart'));

        $this->assertEquals(2, count(session('cart')));

        // 6. View Cart Page
        $response = $this->get(route('cart'));
        $response->assertStatus(200);
        $response->assertSee('Purple Leather Handbag');
        $response->assertSee('Casual Summer Dress');

        // 7. Update Cart Item Quantity via AJAX
        $cartKeys = array_keys(session('cart'));
        $simpleKey = $cartKeys[0]; // Simple product key

        $response = $this->postJson(route('cart.update'), [
            'key' => $simpleKey,
            'quantity' => 3,
        ]);
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Subtotal calculation check:
        // Simple Handbag: 3 * $50.00 = $150.00
        // Variable Dress S: 1 * $30.00 = $30.00
        // Total = $180.00
        $this->assertEquals(3, session('cart')[$simpleKey]['quantity']);

        // 8. Go to Checkout
        $response = $this->get(route('checkout'));
        $response->assertStatus(200);
        $response->assertSee('Inside Dhaka');

        // 9. Place Order
        $response = $this->post(route('checkout.place'), [
            'customer_name' => 'John Doe',
            'customer_phone' => '01712345678',
            'customer_address' => '123 Test Street, Dhaka',
            'delivery_zone' => $this->deliveryZone->zone,
            'payment_method' => 'cod',
            'notes' => 'Deliver in afternoon please.',
        ]);

        $order = Order::first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('order.success', $order->id));

        // Verify Order Details
        $this->assertEquals('John Doe', $order->customer_name);
        $this->assertEquals(180.00, $order->subtotal);
        $this->assertEquals(60.00, $order->delivery_charge_amount);
        $this->assertEquals(240.00, (float) $order->total_amount);
        $this->assertEquals(0.00, (float) $order->discount_amount);
        $this->assertEquals('processing', $order->status);

        // Verify Order Items
        $this->assertEquals(2, $order->items()->count());

        // Verify Stock Decrement
        $this->simpleProduct->refresh();
        $this->variationS->refresh();
        $this->variationM->refresh();
        $this->variableProduct->refresh();

        // Handbag stock: 10 - 3 = 7
        $this->assertEquals(7, $this->simpleProduct->stock_quantity);
        // Variation S stock: 5 - 1 = 4
        $this->assertEquals(4, $this->variationS->stock_quantity);
        // Variation M stock (untouched): 8
        $this->assertEquals(8, $this->variationM->stock_quantity);
        // Variable Product total stock: 4 + 8 = 12
        $this->assertEquals(12, $this->variableProduct->stock_quantity);

        // Cart should be empty now
        $this->assertNull(session('cart'));

        // 10. Visit Order Success Page
        $response = $this->get(route('order.success', $order->id));
        $response->assertStatus(200);
        $response->assertSee('Order Placed Successfully!');
        $response->assertSee('John Doe');

        // --- ADMIN PORTAL TESTS ---

        // 11. Guest should be redirected from Dashboard
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.login'));

        // 12. Show Admin Login Page
        $response = $this->get(route('admin.login'));
        $response->assertStatus(200);
        $response->assertSee('Admin Portal');

        // 13. Login as Admin
        $response = $this->post(route('admin.login.submit'), [
            'email' => 'admin@outfitt.com',
            'password' => 'adminpassword',
        ]);
        $response->assertRedirect(route('admin.dashboard'));

        // 14. Access Dashboard
        $response = $this->actingAs($this->adminUser)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Dashboard Overview');
        $response->assertSee('৳240.00'); // Revenue counts every non-cancelled order
        $response->assertSee('processing'); // Order status in table

        // 15. View Orders list in Admin panel
        $response = $this->actingAs($this->adminUser)->get(route('admin.orders.index'));
        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('৳240'); // The list formats amounts without decimals

        // 16. View Order Detail in Admin Panel
        $response = $this->actingAs($this->adminUser)->get(route('admin.orders.show', $order->id));
        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('Purple Leather Handbag');

        // 17. Update Order Status in Admin Panel
        $response = $this->actingAs($this->adminUser)->post(route('admin.orders.status', $order->id), [
            'status' => 'delivered',
        ]);
        $response->assertRedirect();

        $order->refresh();
        $this->assertEquals('delivered', $order->status);

        // Revenue stays the same while the order is not cancelled.
        $response = $this->actingAs($this->adminUser)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('৳240.00');

        // 18. Logout
        $response = $this->actingAs($this->adminUser)->post(route('admin.logout'));
        $response->assertRedirect(route('admin.login'));
    }
}
