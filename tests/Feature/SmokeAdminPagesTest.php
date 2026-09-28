<?php
namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeAdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_admin_page_renders_for_super_admin_and_moderator()
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $moderator = User::factory()->create(['role' => User::ROLE_MODERATOR]);
        $coupon = Coupon::create(['code' => 'WELCOME10', 'type' => 'percent', 'value' => 10, 'is_active' => true]);
        $order = Order::create([
            'items_json' => json_encode([]),
            'customer_name' => 'Buyer',
            'customer_phone' => '01712345678',
            'customer_address' => 'Dhaka',
            'total_amount' => 470,
            'total' => 470,
            'discount_amount' => 40,
            'coupon_id' => $coupon->id,
            'coupon_code' => 'WELCOME10',
            'status' => 'processing',
        ]);

        $adminUrls = [
            route('admin.dashboard'),
            route('admin.orders.index'),
            route('admin.orders.index', ['range' => 'all', 'status' => 'cancelled', 'per_page' => 50, 'sort' => 'highest']),
            route('admin.orders.show', $order),
            route('admin.coupons.index'),
            route('admin.coupons.create'),
            route('admin.coupons.edit', $coupon),
            route('admin.staff.index'),
            route('admin.staff.create'),
            route('admin.staff.edit', $moderator),
            route('admin.testimonials.index'),
            route('admin.testimonials.create'),
            route('admin.products.index'),
            route('admin.customers.index'),
        ];

        foreach ($adminUrls as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }

        // The moderator only ever sees orders.
        $this->actingAs($moderator)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($moderator)->get(route('admin.orders.index', ['range' => 'last_month']))->assertOk();
        $this->actingAs($moderator)->get(route('admin.orders.show', $order))->assertOk();
        $this->actingAs($moderator)->get(route('admin.orders.export-csv', ['range' => 'all']))->assertOk();
    }

    public function test_storefront_pages_still_render()
    {
        $this->get(route('home'))->assertOk();
        $this->get(route('shop'))->assertOk();
        $this->get(route('categories'))->assertOk();
        $this->get(route('cart'))->assertOk();
        $this->get(route('customer.login'))->assertOk();
        $this->get(route('admin.login'))->assertOk();
    }
}
