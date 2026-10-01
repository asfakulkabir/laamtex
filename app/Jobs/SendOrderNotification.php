<?php

namespace App\Jobs;

use App\Mail\NewOrderNotification;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Mails the admin "new order" alert.
 *
 * Deliberately NOT a ShouldQueue job: this is dispatched with
 * `->afterResponse()` so it runs once the shopper's browser already has the
 * thank-you page, in the same PHP process. Queueing it instead would need a
 * worker on the host, and a missing worker means the alert never arrives.
 *
 * @see \App\Console\Commands\SendPendingOrderNotifications for the retry pass
 */
class SendOrderNotification
{
    use Dispatchable, SerializesModels;

    /**
     * @param  int  $orderId  Only the id is carried so the job never holds a
     *                        stale copy of the order it is about to email.
     */
    public function __construct(public int $orderId)
    {
    }

    public function handle(): void
    {
        $order = Order::find($this->orderId);

        if (! $order || $order->is_notification_sent) {
            return;
        }

        $recipients = $this->recipients();

        if ($recipients === []) {
            Log::warning('Order notification emails not configured in settings, order #' . $order->id . ' not notified.');

            return;
        }

        $delivered = [];

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient)->send(new NewOrderNotification($order));
                $delivered[] = $recipient;
            } catch (\Throwable $e) {
                Log::error('Failed to send order #' . $order->id . ' notification to ' . $recipient . ': ' . $e->getMessage());
            }
        }

        // Only flag the order once at least one inbox actually got the mail,
        // so a temporary SMTP outage leaves it open for the retry pass.
        if ($delivered !== []) {
            $order->forceFill(['is_notification_sent' => true])->saveQuietly();
            Log::info('Order #' . $order->id . ' notification sent to: ' . implode(', ', $delivered));
        }
    }

    /**
     * The comma separated inbox list from the admin settings screen.
     *
     * @return list<string>
     */
    private function recipients(): array
    {
        $emails = (string) Setting::getValue('order_notification_emails', '');

        return array_values(array_filter(array_map('trim', explode(',', $emails))));
    }
}