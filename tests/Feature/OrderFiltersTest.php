<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFiltersTest extends TestCase
{
    use RefreshDatabase;

    private function order(array $attrs = []): Order
    {
        $order = Order::create(array_merge([
            'items_json' => json_encode([]),
            'customer_name' => 'Customer',
            'customer_phone' => '01712345678',
            'customer_address' => 'Dhaka',
            'total_amount' => 100,
            'total' => 100,
            'status' => Order::STATUS_PROCESSING,
        ], $attrs));

        if (isset($attrs['created_at'])) {
            $order->forceFill(['created_at' => $attrs['created_at']])->save();
        }

        return $order;
    }

    public function test_status_and_period_can_be_combined()
    {
        $this->order(['status' => Order::STATUS_CANCELLED, 'created_at' => now()->subDays(3)]);
        $this->order(['status' => Order::STATUS_CANCELLED, 'created_at' => now()->subMonths(2)]);
        $this->order(['status' => Order::STATUS_DELIVERED, 'created_at' => now()->subDays(3)]);

        $response = $this->actingAs(\App\Models\User::factory()->create(['role' => 'super_admin']))
            ->get(route('admin.orders.index', ['range' => '7days', 'status' => 'cancelled']));

        $response->assertOk();
        $this->assertCount(1, $response->viewData('orders'));
        $this->assertSame(Order::STATUS_CANCELLED, $response->viewData('orders')->first()->status);
        // The period totals ignore the status filter on purpose
        $this->assertSame(2, $response->viewData('stats')['total']);
        $this->assertSame(1, (int) $response->viewData('stats')['byStatus']['cancelled']);
        $this->assertSame(1, (int) $response->viewData('stats')['byStatus']['delivered']);
    }

    public function test_period_presets()
    {
        $this->order(['created_at' => now()]);
        $this->order(['created_at' => now()->subDay()]);
        $this->order(['created_at' => now()->subDays(3)]);
        $this->order(['created_at' => now()->subDays(20)]);
        $this->order(['created_at' => now()->subMonths(3)]);

        $admin = \App\Models\User::factory()->create(['role' => 'super_admin']);

        $cases = ['today' => 1, 'yesterday' => 1, '7days' => 3, '30days' => 4];
        foreach ($cases as $range => $expected) {
            $response = $this->actingAs($admin)->get(route('admin.orders.index', ['range' => $range]));
            $this->assertSame($expected, $response->viewData('stats')['total'], "range={$range}");
        }

        $response = $this->actingAs($admin)->get(route('admin.orders.index', ['range' => 'all']));
        $this->assertSame(5, $response->viewData('stats')['total']);
    }

    public function test_custom_range_uses_from_and_to()
    {
        $this->order(['created_at' => now()->subDays(10)]);
        $this->order(['created_at' => now()->subDays(40)]);

        $admin = \App\Models\User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($admin)->get(route('admin.orders.index', [
            'from' => now()->subDays(45)->toDateString(),
            'to' => now()->subDays(20)->toDateString(),
        ]));

        $this->assertSame(1, $response->viewData('stats')['total']);
        $this->assertSame('custom', $response->viewData('period')['key']);
        $this->assertSame('Custom Range', $response->viewData('period')['label']);
    }

    public function test_totals_exclude_cancelled_orders_from_revenue()
    {
        $this->order(['total_amount' => 500, 'status' => Order::STATUS_DELIVERED]);
        $this->order(['total_amount' => 900, 'status' => Order::STATUS_CANCELLED]);

        $admin = \App\Models\User::factory()->create(['role' => 'super_admin']);
        $response = $this->actingAs($admin)->get(route('admin.orders.index', ['range' => 'all']));

        $stats = $response->viewData('stats');
        $this->assertSame(2, $stats['total']);
        $this->assertSame(500.0, $stats['revenue']);
    }

    public function test_filters_survive_pagination()
    {
        foreach (range(1, 20) as $i) {
            $this->order(['customer_name' => 'Buyer ' . $i]);
        }

        $admin = \App\Models\User::factory()->create(['role' => 'super_admin']);
        $response = $this->actingAs($admin)->get(route('admin.orders.index', ['search' => 'Buyer', 'range' => 'all', 'per_page' => 5]));

        $orders = $response->viewData('orders');
        $this->assertSame(20, $orders->total());
        $this->assertStringContainsString('search=Buyer', $orders->nextPageUrl());
    }

    public function test_export_respects_the_current_filters()
    {
        $this->order(['status' => Order::STATUS_CANCELLED, 'customer_name' => 'Cancelled Buyer']);
        $this->order(['status' => Order::STATUS_DELIVERED, 'customer_name' => 'Delivered Buyer']);

        $admin = \App\Models\User::factory()->create(['role' => 'super_admin']);
        $response = $this->actingAs($admin)->get(route('admin.orders.export-csv', ['status' => 'cancelled', 'range' => 'all']));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Cancelled Buyer', $csv);
        $this->assertStringNotContainsString('Delivered Buyer', $csv);
    }

    public function test_modifier_free_period_keys_do_not_break_the_query()
    {
        $this->order();

        $admin = \App\Models\User::factory()->create(['role' => 'super_admin']);
        $response = $this->actingAs($admin)->get(route('admin.orders.index', ['range' => 'DROP TABLE orders']));

        $response->assertOk();
        $this->assertSame('today', $response->viewData('period')['key']);
    }
}
