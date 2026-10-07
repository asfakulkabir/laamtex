<?php

namespace Tests\Feature;

use App\Models\DeliveryCharge;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutOrderTest extends TestCase
{
    use RefreshDatabase;

    private $zone;
    private $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->zone = DeliveryCharge::create([
            'zone' => 'Inside Dhaka',
            'charge' => 70,
            'estimated_days' => '2-3 Days',
        ]);

        $this->product = Product::create([
            'name' => 'Purple Leather Handbag',
            'slug' => 'purple-leather-handbag',
            'price' => 40,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);
    }

    private function seedCart(): void
    {
        $this->withSession(['cart' => [
            'product_' . $this->product->id => [
                'product_id' => $this->product->id,
                'variation_id' => null,
                'name' => $this->product->name,
                'price' => 40,
                'quantity' => 1,
                'variation_details' => null,
                'image' => null,
            ],
        ]]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Test Customer',
            'customer_phone' => '01712345678',
            'customer_address' => '456 Road, Dhaka',
            'delivery_zone' => $this->zone->zone,
            'payment_method' => 'cod',
            'bkash_sender_last4' => '',
        ], $overrides);
    }

    public function test_cod_order_is_created_when_bkash_field_is_submitted_empty()
    {
        $this->seedCart();

        $response = $this->post(route('checkout.place'), $this->payload());

        $order = Order::first();
        $this->assertNotNull($order, 'Order was not created for COD with an empty bKash field.');
        $this->assertSame('Cash on Delivery', $order->payment_method);
        $this->assertNull($order->bkash_sender_last4);
        $this->assertSame(110, $order->total_amount);
        $response->assertRedirect(route('order.success', $order->id));
    }

    public function test_cod_order_is_created_when_bkash_field_is_omitted_entirely()
    {
        $this->seedCart();

        $payload = $this->payload();
        unset($payload['bkash_sender_last4']);

        $this->post(route('checkout.place'), $payload)->assertRedirect();

        $this->assertNotNull(Order::first());
    }

    public function test_bkash_order_is_created_with_four_digits()
    {
        $this->seedCart();

        $this->post(route('checkout.place'), $this->payload([
            'payment_method' => 'bkash',
            'bkash_sender_last4' => '1234',
        ]))->assertRedirect();

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertSame('bKash Send Money', $order->payment_method);
        $this->assertSame('1234', $order->bkash_sender_last4);
    }

    public function test_bkash_order_rejects_missing_or_malformed_digits()
    {
        $this->seedCart();

        $this->post(route('checkout.place'), $this->payload([
            'payment_method' => 'bkash',
            'bkash_sender_last4' => '',
        ]))->assertSessionHasErrors('bkash_sender_last4');

        $this->assertNull(Order::first());

        $this->post(route('checkout.place'), $this->payload([
            'payment_method' => 'bkash',
            'bkash_sender_last4' => '12ab',
        ]))->assertSessionHasErrors('bkash_sender_last4');

        $this->assertNull(Order::first());
    }

    public function test_stock_is_decremented_and_cart_is_cleared()
    {
        $this->seedCart();

        $this->post(route('checkout.place'), $this->payload());

        $this->product->refresh();
        $this->assertSame(9, (int) $this->product->stock_quantity);
        $this->assertNull(session('cart'));
    }

    public function test_customer_note_is_saved_when_provided()
    {
        $this->seedCart();

        $this->post(route('checkout.place'), $this->payload([
            'customer_note' => 'Please deliver after 6 PM.',
        ]))->assertRedirect();

        $this->assertSame('Please deliver after 6 PM.', Order::first()->customer_note);
    }

    public function test_customer_note_is_optional()
    {
        $this->seedCart();

        $this->post(route('checkout.place'), $this->payload())->assertRedirect();

        $this->assertNull(Order::first()->customer_note);
    }

    public function test_customer_note_rejects_more_than_500_characters()
    {
        $this->seedCart();

        $this->post(route('checkout.place'), $this->payload([
            'customer_note' => str_repeat('a', 501),
        ]))->assertSessionHasErrors('customer_note');

        $this->assertNull(Order::first());
    }
}
