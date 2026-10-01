<?php

namespace Tests\Feature;

use App\Jobs\SendOrderNotification;
use App\Mail\NewOrderNotification;
use App\Models\DeliveryCharge;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The admin "new order" mail must never sit between the shopper clicking
 * Confirm and seeing the thank-you page, so it runs once the response has
 * been flushed, with the artisan command as the retry net.
 */
class OrderNotificationMailTest extends TestCase
{
    use RefreshDatabase;

    private $zone;
    private $product;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

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

    private function recipients(string $value): void
    {
        Setting::setValue('order_notification_emails', $value);
    }

    private function placeOrder(): Order
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

        $this->post(route('checkout.place'), [
            'customer_name' => 'Test Customer',
            'customer_phone' => '01712345678',
            'customer_address' => '456 Road, Dhaka',
            'delivery_zone' => $this->zone->zone,
            'payment_method' => 'cod',
            'bkash_sender_last4' => '',
        ])->assertRedirect();

        return Order::firstOrFail();
    }

    /**
     * Every real request is its own PHP process, so a callback registered with
     * ->afterResponse() cannot survive into the next request. The test harness
     * keeps one application alive for the whole test, so drop those callbacks
     * by hand to keep the two requests independent the way production is.
     */
    private function forgetAfterResponseCallbacks(): void
    {
        $callbacks = new \ReflectionProperty($this->app, 'terminatingCallbacks');
        $callbacks->setAccessible(true);
        $callbacks->setValue($this->app, []);
    }

    public function test_placing_an_order_hands_the_mail_off_rather_than_sending_it_inline(): void
    {
        Bus::fake();
        $this->recipients('owner@laamtex.test');

        $this->placeOrder();

        Mail::assertNothingSent();
        Bus::assertDispatched(SendOrderNotification::class);
    }

    public function test_the_mail_goes_out_once_the_response_is_flushed(): void
    {
        $this->recipients('owner@laamtex.test');

        $order = $this->placeOrder();

        Mail::assertSent(NewOrderNotification::class, function (NewOrderNotification $mail) use ($order) {
            return $mail->hasTo('owner@laamtex.test') && $mail->order->is($order);
        });
        $this->assertTrue($order->fresh()->is_notification_sent);
    }

    public function test_every_comma_separated_inbox_is_notified(): void
    {
        $this->recipients(' one@laamtex.test , two@laamtex.test ');

        $this->placeOrder();
        $this->app->terminate();

        Mail::assertSent(NewOrderNotification::class, fn ($mail) => $mail->hasTo('one@laamtex.test'));
        Mail::assertSent(NewOrderNotification::class, fn ($mail) => $mail->hasTo('two@laamtex.test'));
    }

    public function test_an_order_is_never_notified_twice(): void
    {
        $this->recipients('owner@laamtex.test');

        $order = $this->placeOrder();

        (new SendOrderNotification($order->id))->handle();

        Mail::assertSentCount(1);
    }

    public function test_an_unconfigured_inbox_leaves_the_order_open_for_the_retry(): void
    {
        $this->recipients('');

        $order = $this->placeOrder();

        Mail::assertNothingSent();
        $this->assertFalse($order->fresh()->is_notification_sent);
    }

    public function test_the_retry_command_notifies_orders_the_after_response_mail_missed(): void
    {
        $this->recipients('owner@laamtex.test');

        $order = $this->placeOrder();

        // Rewind the order to stand in for a request that died before the
        // after-response mail could be delivered.
        Mail::fake();
        $order->forceFill(['is_notification_sent' => false])->save();

        $this->artisan('orders:notify-pending')
            ->expectsOutputToContain('Notified 1 of 1 pending orders.')
            ->assertSuccessful();

        Mail::assertSent(NewOrderNotification::class, fn ($mail) => $mail->hasTo('owner@laamtex.test'));
        $this->assertTrue($order->fresh()->is_notification_sent);
    }

    public function test_the_retry_command_skips_orders_that_were_already_notified(): void
    {
        $this->recipients('owner@laamtex.test');

        $this->placeOrder();

        Mail::assertSentCount(1);

        $this->artisan('orders:notify-pending')
            ->expectsOutputToContain('No orders are waiting for a notification.')
            ->assertSuccessful();

        Mail::assertSentCount(1);
    }

    public function test_the_thank_you_page_never_sends_the_mail_itself(): void
    {
        $this->recipients('owner@laamtex.test');

        $order = $this->placeOrder();

        // The page the shopper waits on must stay free of SMTP work, and it
        // must not backfill an order another request already notified.
        $order->forceFill(['is_notification_sent' => false])->save();
        Mail::fake();
        $this->forgetAfterResponseCallbacks();

        $this->get(route('order.success', $order->id))->assertOk();

        Mail::assertNothingSent();
        $this->assertFalse($order->fresh()->is_notification_sent);
    }
}