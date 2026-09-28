<?php
namespace Tests\Feature;

use App\Models\{Order, OrderItem, Product, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderImageTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    public function test_order_detail_shows_the_ordered_item_image(): void
    {
        $admin = $this->admin();

        $product = Product::create([
            'user_id' => $admin->id,
            'name' => 'Purple Leather Handbag',
            'slug' => 'purple-leather-handbag',
            'product_type' => 'simple',
            'regular_price' => '39.99',
            'stock_quantity' => 5,
            'is_active' => true,
        ]);
        $product->images()->create(['image' => 'product_images/leather_handbag.png', 'is_featured' => true]);

        $order = Order::create([
            'customer_name' => 'John Doe',
            'customer_phone' => '01712345678',
            'customer_address' => 'Dhaka',
            'total_amount' => 39.99,
            'status' => Order::STATUS_PROCESSING,
            'items_json' => json_encode([[
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => '39.99',
                'quantity' => 1,
                'image' => 'product_images/leather_handbag.png',
            ]]),
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'price' => '39.99',
        ]);

        $html = $this->actingAs($admin)->get(route('admin.orders.show', $order->id))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('product_images/leather_handbag.png', $html);

        // The thumbnail is rendered at the enlarged size and links to the full image.
        $this->assertMatchesRegularExpression(
            '/<a href="[^"]*leather_handbag[^"]*"[^>]*>\s*<img[^>]*width="96"[^>]*height="96"/s',
            $html
        );
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function test_variation_image_wins_over_the_product_image(): void
    {
        $admin = $this->admin();

        $product = Product::create([
            'user_id' => $admin->id,
            'name' => 'Variable Dress',
            'slug' => 'variable-dress',
            'product_type' => 'variable',
            'regular_price' => '30.00',
            'is_active' => true,
        ]);
        $product->images()->create(['image' => 'product_images/parent.png', 'is_featured' => true]);

        $variation = $product->variations()->create([
            'combo_key' => '1',
            'regular_price' => '30.00',
            'status' => 'publish',
            'image' => 'product_variations/red.png',
        ]);

        $order = Order::create([
            'customer_name' => 'Jane Doe',
            'customer_phone' => '01712345678',
            'customer_address' => 'Dhaka',
            'total_amount' => 30.00,
            'status' => Order::STATUS_PROCESSING,
            'items_json' => json_encode([[
                'product_id' => $product->id,
                'variation_id' => $variation->id,
                'name' => $product->name,
                'price' => '30.00',
                'quantity' => 1,
                'variation_details' => 'S, Red',
            ]]),
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variation_id' => $variation->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'price' => '30.00',
            'variation_details' => 'S, Red',
        ]);

        $html = $this->actingAs($admin)->get(route('admin.orders.show', $order->id))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('product_variations/red.png', $html);
    }
}
