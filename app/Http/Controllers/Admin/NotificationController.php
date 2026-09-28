<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Feeds the notification bell in the admin top bar. The panel polls this
 * endpoint, so new orders appear without reloading the page.
 */
class NotificationController extends Controller
{
    /** Never look further back than this when counting "new" orders. */
    private const MAX_WINDOW_DAYS = 7;

    /**
     * Orders placed in the same second the badge was cleared would otherwise be
     * missed, so the watermark is treated as a couple of seconds older.
     */
    private const READ_GRACE_SECONDS = 3;

    public function index(Request $request)
    {
        $since = $this->seenCutoff($request);

        $unread = Order::where('created_at', '>', $since);

        $unseen = (clone $unread)
            ->latest('created_at')
            ->limit(8)
            ->get()
            ->map(fn (Order $order) => $this->payload($order));

        $recent = Order::latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (Order $order) => $this->payload($order));

        return response()->json([
            'unread'   => (clone $unread)->count(),
            'unseen'   => $unseen,
            'recent'   => $recent,
            'checked'  => now()->toIso8601String(),
        ]);
    }

    public function read(Request $request)
    {
        $request->user()->forceFill(['orders_seen_at' => now()])->save();

        return response()->json(['success' => true, 'unread' => 0]);
    }

    /**
     * The watermark used to decide which orders count as "not seen yet".
     */
    private function seenCutoff(Request $request): Carbon
    {
        $floor = now()->subDays(self::MAX_WINDOW_DAYS);
        $seenAt = $request->user()->orders_seen_at;

        if (!$seenAt) {
            return $floor;
        }

        $cutoff = $seenAt->copy()->subSeconds(self::READ_GRACE_SECONDS);

        return $cutoff->greaterThan($floor) ? $cutoff : $floor;
    }

    private function payload(Order $order): array
    {
        return [
            'id'         => $order->id,
            'name'       => $order->customer_name,
            'phone'      => $order->customer_phone,
            'total'      => (float) $order->total_amount,
            'status'     => $order->status,
            'status_label' => Order::STATUSES[$order->status] ?? ucfirst($order->status),
            'created_at' => $order->created_at->diffForHumans(),
            'url'        => route('admin.orders.show', $order->id),
        ];
    }
}
