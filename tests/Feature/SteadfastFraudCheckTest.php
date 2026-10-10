<?php

namespace Tests\Feature;

use App\Models\DeliveryCharge;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\SteadfastService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The Steadfast fraud check normalises the customer number and surfaces the
 * delivered / cancelled / fraud report counters next to the send button.
 */
class SteadfastFraudCheckTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    private function configureApi(): void
    {
        Setting::setValue('steadfast_api_key', 'test-api-key');
        Setting::setValue('steadfast_secret_key', 'test-secret-key');
    }

    private function makeOrder(string $phone = '01712345678'): Order
    {
        // zone is unique, so each order gets its own row within one test.
        $zone = DeliveryCharge::create([
            'zone' => 'Zone ' . uniqid(),
            'charge' => 70,
            'estimated_days' => '2-3 Days',
        ]);

        $product = Product::create([
            'name' => 'Fraud Check Tee',
            'slug' => 'fraud-check-tee',
            'price' => 500,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        return Order::create([
            'items_json' => json_encode([]),
            'payment_method' => 'Cash on Delivery',
            'customer_name' => 'Fraud Check Customer',
            'customer_phone' => $phone,
            'customer_address' => '456 Road, Dhaka',
            'delivery_charge_id' => $zone->id,
            'total_amount' => 570,
            'total' => 570,
            'status' => 'processing',
            'is_sent_to_steadfast' => false,
            'is_notification_sent' => false,
        ]);
    }

    public static function phoneProvider(): array
    {
        return [
            'local format' => ['01712345678', '01712345678'],
            'country code' => ['+8801712345678', '01712345678'],
            'no plus' => ['8801712345678', '01712345678'],
            'spaces and dashes' => ['017-1234 5678', '01712345678'],
        ];
    }

    /** @dataProvider phoneProvider */
    public function test_it_reduces_any_phone_form_to_the_last_eleven_digits(string $input, string $expected)
    {
        $this->assertSame($expected, SteadfastService::normalisePhone($input));
    }

    public function test_it_reads_the_counts_from_the_documented_fraud_check_payload()
    {
        // GET /fraud_check/{phone} -- the endpoint that actually carries the
        // parcel tallies, unlike /fraud_check/score/{phone} which is ratios only.
        $this->configureApi();

        Http::fake([
            '*/fraud_check/*' => Http::response([
                'status' => 200,
                'message' => 'Fraud check successful',
                'phone' => '01756627489',
                'Total_parcels' => 9,
                'total_delivered' => 8,
                'total_cancelled' => 1,
                'total_fraud_reports' => [],
            ]),
        ]);

        $order = $this->makeOrder('01756627489');

        $this->actingAs($this->admin())
            ->getJson(route('admin.orders.fraud-check', $order))
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('phone', '01756627489')
            ->assertJsonPath('total_parcels', 9)
            ->assertJsonPath('total_delivered', 8)
            ->assertJsonPath('total_cancelled', 1)
            ->assertJsonPath('total_fraud_reports', 0);
    }

    /**
     * The live response captured from the browser network tab for
     * GET /fraud_check/{phone}. This is the shape that drives the three tiles.
     */
    public function test_it_fills_the_tiles_from_the_live_inspected_payload()
    {
        $this->configureApi();

        Http::fake([
            '*/fraud_check/*' => Http::response([
                'delivery_ratio' => 50,
                'cancellation_ratio' => 50,
                'volume_band' => 'low',
                'volume_range' => '2',
                'fraud_reports' => 0,
                'fraud_categories' => [],
                'fraud_keywords' => [],
                'frauds' => [],
                'reported_by_you' => false,
                'cancelled_count' => 1,
                'delivered_count' => 1,
            ]),
        ]);

        $order = $this->makeOrder('01712345678');

        $this->actingAs($this->admin())
            ->getJson(route('admin.orders.fraud-check', $order))
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('total_delivered', 1)
            ->assertJsonPath('total_cancelled', 1)
            ->assertJsonPath('total_fraud_reports', 0)
            ->assertJsonPath('total_parcels', 2)
            ->assertJsonPath('volume_range', '2')
            ->assertJsonPath('delivery_ratio', 50);
    }

    /**
     * Steadfast caps volume_range as "50+" for a long history, so it must show
     * as text and must not be read as an exact count of 50.
     */
    public function test_a_capped_volume_range_is_kept_as_text()
    {
        $this->configureApi();

        Http::fake([
            '*/fraud_check/*' => Http::response([
                'status' => 200,
                'phone' => '01712345678',
                'delivery_ratio' => 92,
                'cancellation_ratio' => 7,
                'volume_band' => 'high',
                'volume_range' => '50+',
                'total_reports' => 0,
                'fraud_categories' => [],
                'score' => null,
                'level' => null,
                'reasons' => [],
                'scoring_disabled' => true,
                'doubtful_reports' => false,
            ]),
        ]);

        $order = $this->makeOrder('01712345678');

        $this->actingAs($this->admin())
            ->getJson(route('admin.orders.fraud-check', $order))
            ->assertOk()
            ->assertJsonPath('volume_range', '50+')
            ->assertJsonPath('total_parcels', null);
    }

    /**
     * The score endpoint sends ratios and no counts, so the tiles are rebuilt
     * from the volume. 9 parcels at 88% delivered and 11% cancelled is 8 and 1.
     */
    public function test_the_score_endpoint_counts_are_derived_from_the_ratios()
    {
        $this->configureApi();

        Http::fake([
            '*/fraud_check/*' => Http::response([
                'status' => 200,
                'phone' => '01756627489',
                'score' => null,
                'level' => null,
                'reasons' => [],
                'scoring_disabled' => true,
                'doubtful_reports' => false,
                'total_reports' => 0,
                'delivery_ratio' => 88,
                'cancellation_ratio' => 11,
                'volume_band' => 'medium',
                'volume_range' => '9',
                'fraud_categories' => [],
                'return_ratio' => 11,
            ]),
        ]);

        $order = $this->makeOrder('01756627489');

        $this->actingAs($this->admin())
            ->getJson(route('admin.orders.fraud-check', $order))
            ->assertOk()
            ->assertJsonPath('total_parcels', 9)
            ->assertJsonPath('total_delivered', 8)
            ->assertJsonPath('total_cancelled', 1)
            ->assertJsonPath('derived', true);
    }

    public function test_it_finds_the_counters_under_key_names_it_has_not_seen_before()
    {
        // Steadfast has shipped at least delivered_count, total_delivered and
        // delivered for the same endpoint. The pattern fallback keeps the tiles
        // filled if the field is renamed again.
        $this->configureApi();

        Http::fake([
            '*/fraud_check/*' => Http::response([
                'delivery_ratio' => 88,
                'cancellation_ratio' => 11,
                'volume_band' => 'medium',
                'volume_range' => '9',
                'delivered_parcels' => 8,
                'cancelled_parcels' => 1,
                'fraud_reports' => 0,
            ]),
        ]);

        $order = $this->makeOrder('01769021221');

        $this->actingAs($this->admin())
            ->getJson(route('admin.orders.fraud-check', $order))
            ->assertOk()
            ->assertJsonPath('total_parcels', 9)
            ->assertJsonPath('total_delivered', 8)
            ->assertJsonPath('total_cancelled', 1)
            ->assertJsonPath('total_fraud_reports', 0);
    }

public function test_a_ratio_is_never_mistaken_for_a_parcel_count()
    {
        $this->configureApi();

        Http::fake([
            '*/fraud_check/*' => Http::response([
                'delivery_ratio' => 88,
                'cancellation_ratio' => 11,
                'volume_range' => '9',
            ]),
        ]);

        $order = $this->makeOrder('01756627489');

        // 8 and 1 come from 9 x the ratios, not from the ratio values (88/11)
        // being mistaken for counts.
        $this->actingAs($this->admin())
            ->getJson(route('admin.orders.fraud-check', $order))
            ->assertOk()
            ->assertJsonPath('total_delivered', 8)
            ->assertJsonPath('total_cancelled', 1);
    }

    /**
     * "50+" is a floor, not a total, so it cannot be multiplied out. Inventing a
     * figure from it would be worse than showing nothing.
     */
    public function test_a_capped_volume_yields_no_derived_counts()
    {
        $this->configureApi();

        Http::fake([
            '*/fraud_check/*' => Http::response([
                'status' => 200,
                'phone' => '01712345678',
                'delivery_ratio' => 92,
                'cancellation_ratio' => 7,
                'volume_band' => 'high',
                'volume_range' => '50+',
                'total_reports' => 0,
            ]),
        ]);

        $order = $this->makeOrder('01712345678');

        $this->actingAs($this->admin())
            ->getJson(route('admin.orders.fraud-check', $order))
            ->assertOk()
            ->assertJsonPath('volume_range', '50+')
            ->assertJsonPath('total_parcels', null)
            ->assertJsonPath('total_delivered', null)
            ->assertJsonPath('total_cancelled', null)
            ->assertJsonPath('derived', false);
    }

    public function test_it_reads_the_counts_from_a_fraud_check_payload()
    {
        $this->configureApi();

        Http::fake([
            '*/fraud_check/*' => Http::response([
                'status' => 200,
                'message' => 'Score Successfully Retrieved',
                'phone' => '01712345678',
                'Total_parcels' => 17,
                'total_delivered' => 7,
                'total_cancelled' => 10,
                'total_fraud_reports' => [],
            ]),
        ]);

        $order = $this->makeOrder();

        $response = $this->actingAs($this->admin())->getJson(route('admin.orders.fraud-check', $order));

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('phone', '01712345678')
            ->assertJsonPath('total_parcels', 17)
            ->assertJsonPath('total_delivered', 7)
            ->assertJsonPath('total_cancelled', 10)
            ->assertJsonPath('total_fraud_reports', 0);
    }

    public function test_it_reads_the_counts_from_a_score_payload_nested_under_scorecard()
    {
        $this->configureApi();

        Http::fake([
            '*/fraud_check/*' => Http::response([
                'status' => 200,
                'scorecard' => [
                    'phone' => '01712345678',
                    'delivery_ratio' => 92,
                    'cancellation_ratio' => 7,
                    'volume_band' => 'high',
                    'total_reports' => 3,
                    'fraud_categories' => ['fake_id', 'fake_order'],
                    'level' => 'high',
                ],
            ]),
        ]);

        $order = $this->makeOrder();

        $response = $this->actingAs($this->admin())->getJson(route('admin.orders.fraud-check', $order));

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('total_fraud_reports', 3)
            ->assertJsonPath('volume_band', 'high')
            ->assertJsonPath('level', 'high')
            ->assertJsonPath('delivery_ratio', 92);
    }

    public function test_missing_counters_stay_null_instead_of_becoming_zero()
    {
        $this->configureApi();

        Http::fake([
            '*/fraud_check/*' => Http::response([
                'status' => 200,
                'phone' => '01712345678',
                'total_reports' => 0,
            ]),
        ]);

        $order = $this->makeOrder();

        $response = $this->actingAs($this->admin())->getJson(route('admin.orders.fraud-check', $order));

        $response->assertOk()
            ->assertJsonPath('total_delivered', null)
            ->assertJsonPath('total_cancelled', null);
    }

    public function test_an_invalid_number_is_rejected_without_calling_steadfast()
    {
        $this->configureApi();

        Http::fake(['*' => Http::response(['status' => 200])]);

        $order = $this->makeOrder('12345');

        $this->actingAs($this->admin())
            ->getJson(route('admin.orders.fraud-check', $order))
            ->assertOk()
            ->assertJsonPath('ok', false);

        Http::assertNothingSent();
    }

    public function test_it_reports_a_failure_instead_of_throwing_when_steadfast_errors()
    {
        $this->configureApi();

        Http::fake(['*/fraud_check/*' => Http::response('boom', 500)]);

        $order = $this->makeOrder();

        $this->actingAs($this->admin())
            ->getJson(route('admin.orders.fraud-check', $order))
            ->assertOk()
            ->assertJsonPath('ok', false);
    }

    public function test_the_lookup_is_reused_for_the_same_number()
    {
        $this->configureApi();

        Http::fake([
            '*/fraud_check/*' => Http::response([
                'status' => 200,
                'total_delivered' => 5,
                'total_cancelled' => 1,
            ]),
        ]);

        $first = $this->makeOrder('01711111111');
        $second = $this->makeOrder('01711111111');
        $admin = $this->admin();

        $this->actingAs($admin)->getJson(route('admin.orders.fraud-check', $first))->assertOk();
        $this->actingAs($admin)->getJson(route('admin.orders.fraud-check', $second))
            ->assertOk()
            ->assertJsonPath('total_delivered', 5);

        Http::assertSentCount(1);
    }

    public function test_the_fraud_check_renders_under_the_phone_in_the_order_dropdown()
    {
        $order = $this->makeOrder();

        $html = $this->actingAs($this->admin())
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('js-fraud-check-toggle', false)
            ->assertSee(route('admin.orders.fraud-check', $order), false)
            ->getContent();

        $phoneAt = strpos($html, '>Phone<');
        $fraudAt = strpos($html, 'js-fraud-check-toggle');

        $this->assertNotFalse($phoneAt);
        $this->assertNotFalse($fraudAt);
        $this->assertGreaterThan($phoneAt, $fraudAt, 'The fraud check should render after the Phone label.');
        $this->assertLessThan(
            strpos($html, '>Payment<'),
            $fraudAt,
            'The fraud check should stay inside the Phone column, before Payment.'
        );
    }

    public function test_the_fraud_check_renders_on_the_single_order_page()
    {
        $order = $this->makeOrder();

        $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('js-fraud-check-toggle', false)
            ->assertSee(route('admin.orders.fraud-check', $order), false);
    }

    public function test_the_panel_shows_the_number_as_stored_on_the_order()
    {
        $order = $this->makeOrder('+8801756627489');

        $html = $this->actingAs($this->admin())
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->getContent();

        // The +88 prefix must survive into the panel rather than being shown
        // stripped down to the API's 11 digit form.
        $this->assertStringContainsString(
            'js-fraud-check-phone font-mono text-cyan-300">+8801756627489<',
            $html
        );
    }

    public function test_guests_cannot_run_a_fraud_check()
    {
        $order = $this->makeOrder();

        $this->get(route('admin.orders.fraud-check', $order))
            ->assertRedirect(route('admin.login'));
    }
}