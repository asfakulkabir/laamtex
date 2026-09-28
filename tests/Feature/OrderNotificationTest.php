<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $attrs = []): Order
    {
        return Order::create(array_merge([
            'items_json' => json_encode([]),
            'customer_name' => 'Customer',
            'customer_phone' => '01712345678',
            'customer_address' => 'Dhaka',
            'total_amount' => 500,
            'status' => Order::STATUS_PROCESSING,
        ], $attrs));
    }

    public function test_bell_endpoint_counts_orders_after_the_last_seen_moment()
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'orders_seen_at' => now()->subMinutes(30),
        ]);

        $this->makeOrder();
        $this->makeOrder();

        $response = $this->actingAs($admin)->getJson(route('admin.notifications.index'));
        $response->assertOk()->assertJsonStructure(['unread', 'unseen', 'recent', 'checked']);
        $this->assertSame(2, $response->json('unread'));
        $this->assertCount(2, $response->json('unseen'));
    }

    public function test_seen_orders_are_excluded_from_the_badge()
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'orders_seen_at' => now(),
        ]);

        // The admin looked at this order a few seconds after it arrived
        $this->makeOrder();
        $admin->forceFill(['orders_seen_at' => now()->addSeconds(10)])->save();

        $response = $this->actingAs($admin)->getJson(route('admin.notifications.index'));
        $this->assertSame(0, $response->json('unread'));
        $this->assertCount(0, $response->json('unseen'));
        $this->assertCount(1, $response->json('recent'));
    }

    public function test_marking_as_read_clears_the_badge()
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'orders_seen_at' => now()->subDay(),
        ]);
        $order = $this->makeOrder();
        $order->forceFill(['created_at' => now()->subHour()])->save();

        $this->actingAs($admin)->getJson(route('admin.notifications.index'))->assertJson(['unread' => 1]);

        $this->actingAs($admin)->postJson(route('admin.notifications.read'))->assertOk();

        $this->assertNotNull($admin->fresh()->orders_seen_at);
        $this->actingAs($admin)->getJson(route('admin.notifications.index'))->assertJson(['unread' => 0]);
    }

    public function test_the_window_never_looks_back_more_than_seven_days()
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'orders_seen_at' => now()->subYear(),
        ]);

        $old = $this->makeOrder();
        $old->forceFill(['created_at' => now()->subDays(30)])->save();
        $this->makeOrder();

        $this->actingAs($admin)->getJson(route('admin.notifications.index'))
            ->assertJson(['unread' => 1]);
    }

    public function test_moderator_also_gets_notifications()
    {
        $moderator = User::factory()->create([
            'role' => User::ROLE_MODERATOR,
            'orders_seen_at' => now()->subDay(),
        ]);
        $this->makeOrder();

        $this->actingAs($moderator)->getJson(route('admin.notifications.index'))
            ->assertOk()
            ->assertJson(['unread' => 1]);
    }

    public function test_an_order_placed_in_the_same_second_as_mark_read_is_not_missed()
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'orders_seen_at' => now(),
        ]);

        $this->makeOrder();

        $this->actingAs($admin)->getJson(route('admin.notifications.index'))
            ->assertJson(['unread' => 1]);
    }

    public function test_customers_cannot_read_the_endpoint()
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->getJson(route('admin.notifications.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_login_initialises_the_seen_watermark()
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $admin->forceFill(['orders_seen_at' => null])->save();
        $this->makeOrder();

        $this->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertNotNull($admin->fresh()->orders_seen_at);
    }

    public function test_every_admin_page_renders_the_bell()
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        foreach ([
            route('admin.dashboard'),
            route('admin.orders.index'),
            route('admin.coupons.index'),
            route('admin.staff.index'),
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk()->assertSee('orderNotifications()', false);
        }
    }
}
