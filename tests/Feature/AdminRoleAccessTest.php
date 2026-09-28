<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\DeliveryCharge;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    private function moderator(): User
    {
        return User::factory()->create(['role' => User::ROLE_MODERATOR]);
    }

    private function order(array $attrs = []): Order
    {
        return Order::create(array_merge([
            'items_json' => json_encode([]),
            'customer_name' => 'Test Customer',
            'customer_phone' => '01712345678',
            'customer_address' => 'Dhaka',
            'total_amount' => 100,
            'total' => 100,
            'status' => Order::STATUS_PROCESSING,
        ], $attrs));
    }

    public function test_is_admin_flag_stays_in_sync_with_role()
    {
        $admin = $this->superAdmin();
        $moderator = $this->moderator();
        $customer = User::factory()->create();

        $this->assertTrue($admin->fresh()->is_admin);
        $this->assertTrue($moderator->fresh()->is_admin);
        $this->assertFalse($customer->fresh()->is_admin);

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->isSuperAdmin());
        $this->assertTrue($moderator->isAdmin());
        $this->assertFalse($moderator->isSuperAdmin());
        $this->assertTrue($customer->isCustomer());
    }

    public function test_moderator_can_open_orders_and_change_status()
    {
        $moderator = $this->moderator();
        $order = $this->order();

        $this->actingAs($moderator)->get(route('admin.orders.index'))->assertOk();
        $this->actingAs($moderator)->get(route('admin.orders.show', $order))->assertOk();

        $this->actingAs($moderator)
            ->post(route('admin.orders.status', $order), ['status' => Order::STATUS_SHIPPED])
            ->assertRedirect(route('admin.orders.index'));

        $this->assertSame(Order::STATUS_SHIPPED, $order->fresh()->status);
    }

    public function test_moderator_cannot_delete_an_order()
    {
        $moderator = $this->moderator();
        $order = $this->order();

        $this->actingAs($moderator)
            ->delete(route('admin.orders.destroy', $order))
            ->assertRedirect(route('admin.orders.index'));

        $this->assertSame(1, Order::count());
    }

    public function test_super_admin_can_delete_an_order()
    {
        $order = $this->order();

        $this->actingAs($this->superAdmin())
            ->delete(route('admin.orders.destroy', $order))
            ->assertRedirect(route('admin.orders.index'));

        $this->assertSame(0, Order::count());
    }

    public function test_moderator_is_locked_out_of_every_other_section()
    {
        $moderator = $this->moderator();

        $restricted = [
            'admin.products.index',
            'admin.categories.index',
            'admin.customers.index',
            'admin.coupons.index',
            'admin.staff.index',
            'admin.sliders.index',
            'admin.delivery-charges.index',
            'admin.settings.edit',
        ];

        foreach ($restricted as $routeName) {
            $this->actingAs($moderator)
                ->get(route($routeName))
                ->assertRedirect(route('admin.orders.index'));
        }
    }

    public function test_super_admin_reaches_every_section()
    {
        $admin = $this->superAdmin();

        $allowed = [
            'admin.products.index',
            'admin.categories.index',
            'admin.customers.index',
            'admin.coupons.index',
            'admin.staff.index',
            'admin.sliders.index',
            'admin.delivery-charges.index',
            'admin.settings.edit',
        ];

        foreach ($allowed as $routeName) {
            $this->actingAs($admin)->get(route($routeName))->assertOk();
        }
    }

    public function test_customer_cannot_reach_the_admin_panel()
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get(route('admin.orders.index'))->assertRedirect(route('admin.login'));
        $this->actingAs($customer)->get(route('admin.products.index'))->assertRedirect(route('admin.login'));
    }

    public function test_super_admin_manages_staff()
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.staff.store'), [
            'name' => 'New Moderator',
            'email' => 'mod@example.com',
            'phone' => '01711111111',
            'role' => User::ROLE_MODERATOR,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertRedirect(route('admin.staff.index'));

        $moderator = User::where('email', 'mod@example.com')->first();
        $this->assertNotNull($moderator);
        $this->assertTrue($moderator->isModerator());
        $this->assertTrue($moderator->is_admin);
    }

    public function test_the_last_super_admin_cannot_be_demoted_or_removed()
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->put(route('admin.staff.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => User::ROLE_MODERATOR,
        ])->assertSessionHasErrors('role');

        $this->assertTrue($admin->fresh()->isSuperAdmin());

        $this->actingAs($admin)->delete(route('admin.staff.destroy', $admin))->assertRedirect();
        $this->assertNotNull($admin->fresh());
    }
}
