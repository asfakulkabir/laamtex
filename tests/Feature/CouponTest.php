<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\DeliveryCharge;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    private $zone;
    private $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->zone = DeliveryCharge::create(['zone' => 'Inside Dhaka', 'charge' => 70]);
        $this->product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => 400,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);
    }

    private function seedCart(int $price = 400, int $qty = 1): void
    {
        $this->withSession(['cart' => [
            'product_' . $this->product->id => [
                'product_id' => $this->product->id,
                'variation_id' => null,
                'name' => $this->product->name,
                'price' => $price,
                'quantity' => $qty,
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

    public function test_percentage_coupon_is_applied_to_the_order()
    {
        $coupon = Coupon::create(['code' => 'SAVE10', 'type' => 'percent', 'value' => 10, 'is_active' => true]);

        $this->seedCart();
        $this->post(route('checkout.coupon.apply'), ['code' => 'save10'])
            ->assertOk()
            ->assertJson(['success' => true, 'discount' => 40]);

        $this->post(route('checkout.place'), $this->payload())->assertRedirect();

        $order = \App\Models\Order::first();
        $this->assertSame('SAVE10', $order->coupon_code);
        $this->assertSame($coupon->id, $order->coupon_id);
        $this->assertSame(40.0, (float) $order->discount_amount);
        // 400 subtotal - 40 discount + 70 delivery
        $this->assertSame(430, $order->total_amount);
        $this->assertSame(400.0, (float) $order->subtotal);
        $this->assertSame(1, $coupon->fresh()->used_count);
    }

    public function test_fixed_coupon_cannot_discount_more_than_the_subtotal()
    {
        Coupon::create(['code' => 'FLAT1000', 'type' => 'fixed', 'value' => 1000, 'is_active' => true]);

        $this->seedCart(400);
        $this->post(route('checkout.coupon.apply'), ['code' => 'FLAT1000'])->assertOk();

        $this->post(route('checkout.place'), $this->payload())->assertRedirect();

        $order = \App\Models\Order::first();
        $this->assertSame(400.0, (float) $order->discount_amount);
        $this->assertSame(70, $order->total_amount);
    }

    public function test_percentage_coupon_respects_the_maximum_discount_cap()
    {
        Coupon::create([
            'code' => 'BIG20',
            'type' => 'percent',
            'value' => 20,
            'max_discount_amount' => 50,
            'is_active' => true,
        ]);

        $this->seedCart(1000);
        $this->post(route('checkout.coupon.apply'), ['code' => 'BIG20'])
            ->assertOk()
            ->assertJson(['discount' => 50]);
    }

    public function test_invalid_coupon_is_rejected()
    {
        $this->seedCart();
        $this->post(route('checkout.coupon.apply'), ['code' => 'NOPE'])
            ->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_minimum_order_is_enforced()
    {
        Coupon::create(['code' => 'BIGONLY', 'type' => 'fixed', 'value' => 50, 'min_order_amount' => 1000, 'is_active' => true]);

        $this->seedCart(400);
        $this->post(route('checkout.coupon.apply'), ['code' => 'BIGONLY'])
            ->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_expired_coupon_is_rejected()
    {
        Coupon::create([
            'code' => 'OLD',
            'type' => 'percent',
            'value' => 10,
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $this->seedCart();
        $this->post(route('checkout.coupon.apply'), ['code' => 'OLD'])->assertStatus(422);
    }

    public function test_usage_limit_is_enforced()
    {
        $coupon = Coupon::create(['code' => 'ONCE', 'type' => 'percent', 'value' => 10, 'usage_limit' => 1, 'is_active' => true]);
        $coupon->forceFill(['used_count' => 1])->save();

        $this->seedCart();
        $this->post(route('checkout.coupon.apply'), ['code' => 'ONCE'])->assertStatus(422);
    }

    public function test_per_user_limit_is_enforced_for_logged_in_customers()
    {
        $customer = User::factory()->create();
        $coupon = Coupon::create(['code' => 'FIRSTONLY', 'type' => 'percent', 'value' => 10, 'per_user_limit' => 1, 'is_active' => true]);

        \App\Models\Order::create([
            'user_id' => $customer->id,
            'items_json' => json_encode([]),
            'customer_name' => 'Customer',
            'customer_phone' => '01712345678',
            'customer_address' => 'Dhaka',
            'coupon_id' => $coupon->id,
            'coupon_code' => 'FIRSTONLY',
            'discount_amount' => 10,
            'total_amount' => 100,
            'status' => 'delivered',
        ]);

        $this->seedCart();
        $this->actingAs($customer)->post(route('checkout.coupon.apply'), ['code' => 'FIRSTONLY'])->assertStatus(422);
    }

    public function test_coupon_is_ignored_when_removed_from_the_session()
    {
        Coupon::create(['code' => 'SAVE10', 'type' => 'percent', 'value' => 10, 'is_active' => true]);

        $this->seedCart();
        $this->post(route('checkout.coupon.remove'))->assertOk();

        $this->post(route('checkout.place'), $this->payload())->assertRedirect();

        $order = \App\Models\Order::first();
        $this->assertNull($order->coupon_code);
        $this->assertSame(0.0, (float) $order->discount_amount);
        $this->assertSame(470, $order->total_amount);
    }

    public function test_checkout_page_shows_the_applied_coupon()
    {
        Coupon::create(['code' => 'SAVE10', 'type' => 'percent', 'value' => 10, 'is_active' => true]);

        $this->seedCart();
        $this->post(route('checkout.coupon.apply'), ['code' => 'SAVE10'])->assertOk();

        $response = $this->get(route('checkout'));
        $response->assertOk();
        $response->assertSee('SAVE10');
        $this->assertSame(40.0, (float) $response->viewData('discount'));
    }

    public function test_super_admin_can_manage_coupons_but_moderator_cannot()
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        $this->actingAs($admin)->get(route('admin.coupons.create'))->assertOk();

        $this->actingAs($admin)->post(route('admin.coupons.store'), [
            'code' => 'EID2026',
            'description' => 'Eid sale',
            'type' => 'percent',
            'value' => 15,
            'is_active' => 1,
        ])->assertRedirect(route('admin.coupons.index'));

        $coupon = Coupon::where('code', 'EID2026')->first();
        $this->assertNotNull($coupon);
        $this->assertSame('15%', $coupon->value_label);

        $moderator = User::factory()->create(['role' => User::ROLE_MODERATOR]);
        $this->actingAs($moderator)->get(route('admin.coupons.index'))->assertRedirect(route('admin.orders.index'));
        $this->actingAs($moderator)->post(route('admin.coupons.store'), [
            'code' => 'HACK', 'type' => 'percent', 'value' => 90, 'is_active' => 1,
        ])->assertRedirect(route('admin.orders.index'));
        $this->assertNull(Coupon::where('code', 'HACK')->first());
    }

    public function test_coupon_code_must_be_unique()
    {
        Coupon::create(['code' => 'TAKEN', 'type' => 'percent', 'value' => 10, 'is_active' => true]);
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        $this->actingAs($admin)->post(route('admin.coupons.store'), [
            'code' => 'TAKEN', 'type' => 'percent', 'value' => 20, 'is_active' => 1,
        ])->assertSessionHasErrors('code');
    }

    public function test_percentage_coupon_cannot_exceed_100()
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        $this->actingAs($admin)->post(route('admin.coupons.store'), [
            'code' => 'TOOMUCH', 'type' => 'percent', 'value' => 150, 'is_active' => 1,
        ])->assertSessionHasErrors('value');
    }
}
