<?php

use App\Jobs\SendOrderNotification;
use App\Models\Order;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/**
 * Safety net for orders whose notification never made it out.
 *
 * The normal path dispatches the mail right after the shopper's response is
 * flushed, so it almost always lands. If the PHP process dies mid-request or
 * SMTP is down, the order keeps `is_notification_sent = false` and this
 * command picks it up on the next run.
 */
Artisan::command('orders:notify-pending', function () {
    $orders = Order::where('is_notification_sent', false)
        ->where('created_at', '>=', now()->subDays(7))
        ->orderBy('id')
        ->limit(200)
        ->get();

    if ($orders->isEmpty()) {
        $this->info('No orders are waiting for a notification.');

        return;
    }

    $notified = 0;

    foreach ($orders as $order) {
        (new SendOrderNotification($order->id))->handle();
        $order->refresh();

        if ($order->is_notification_sent) {
            $notified++;
        }
    }

    $this->info("Notified {$notified} of {$orders->count()} pending orders.");
})->purpose('Send the admin new-order email for any order still waiting for it');

Schedule::command('orders:notify-pending')->everyFiveMinutes()->withoutOverlapping();