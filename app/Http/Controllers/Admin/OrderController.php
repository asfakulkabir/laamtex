<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\SteadfastService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Period presets offered on the orders page.
     */
    public const PERIODS = [
        'today'      => 'Today',
        'yesterday'  => 'Yesterday',
        '7days'      => 'Last 7 Days',
        '30days'     => 'Last 30 Days',
        'this_month' => 'This Month',
        'last_month' => 'Last Month',
        'custom'     => 'Custom Range',
        'all'        => 'All Time',
    ];

    public const SORTS = [
        'newest' => 'Newest first',
        'oldest' => 'Oldest first',
        'highest' => 'Highest amount',
        'lowest' => 'Lowest amount',
    ];

    public function index(Request $request)
    {
        // The list respects every filter, including status.
        [$query, $period] = $this->filteredQuery($request);

        // The headline numbers describe the whole period, so the status filter
        // is deliberately left out of them (that is what makes it possible to
        // jump from "how many cancelled?" to that exact list).
        [$periodQuery] = $this->filteredQuery($request, withStatus: false);

        $sort = array_key_exists($request->input('sort'), self::SORTS) ? $request->input('sort') : 'newest';
        $perPage = (int) $request->input('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100], true)) {
            $perPage = 15;
        }

        $query->with('deliveryCharge');

        match ($sort) {
            'oldest'  => $query->oldest('created_at'),
            'highest' => $query->orderByDesc('total_amount'),
            'lowest'  => $query->orderBy('total_amount'),
            default   => $query->latest('created_at'),
        };

        $orders = $query->paginate($perPage)->withQueryString();

        $statusCounts = (clone $periodQuery)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $stats = [
            'total'    => (clone $periodQuery)->count(),
            'revenue'  => (float) (clone $periodQuery)->where('status', '!=', Order::STATUS_CANCELLED)->sum('total_amount'),
            'discount' => (float) (clone $periodQuery)->sum('discount_amount'),
            'byStatus' => $statusCounts,
        ];

        return view('admin.orders.index', compact('orders', 'period', 'sort', 'perPage', 'stats'));
    }

    public function show(Order $order)
    {
        $order->load('items.product', 'items.variation', 'deliveryCharge');
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:processing,shipped,delivered,cancelled',
        ]);

        $order->update([
            'status' => $request->input('status'),
        ]);

        return redirect()->route('admin.orders.index')
            ->with('success', "Order #{$order->id} status updated to " . (Order::STATUSES[$order->status] ?? ucfirst($order->status)) . '.');
    }

    /**
     * Steadfast delivery history for the customer number on this order. Kept as
     * JSON and fetched on demand so the order list does not fire one API call
     * per row on every page load.
     */
    public function fraudCheck(Order $order, SteadfastService $steadfast)
    {
        return response()->json($steadfast->fraudCheck($order->customer_phone));
    }

    public function sendToSteadfast(Order $order, SteadfastService $steadfast)
    {
        if ($order->is_sent_to_steadfast) {
            return redirect()->back()->with('error', "Order #{$order->id} has already been sent to Steadfast.");
        }

        if (!$steadfast->isConfigured()) {
            return redirect()->back()->with('error', 'Steadfast API credentials are not configured. Please add them in Settings.');
        }

        $deliveryCharge = $order->deliveryCharge;
        $chargeAmount = $deliveryCharge ? $deliveryCharge->charge : 0;

        $response = $steadfast->createOrder([
            'invoice' => 'ORDER-' . $order->id,
            'recipient_name' => $order->customer_name,
            'recipient_phone' => $order->customer_phone,
            'recipient_address' => $order->customer_address,
            'cod_amount' => $order->total_amount,
            'note' => 'Delivery charge: ৳' . $chargeAmount,
        ]);

        if (($response['status'] ?? 0) === 200) {
            $consignment = $response['consignment'] ?? [];
            $order->update([
                'is_sent_to_steadfast' => true,
                'steadfast_consignment_id' => $consignment['consignment_id'] ?? null,
                'status' => Order::STATUS_SHIPPED,
            ]);

            return redirect()->back()->with('success', "Order #{$order->id} sent to Steadfast successfully. Tracking: " . ($consignment['tracking_code'] ?? 'N/A'));
        }

        return redirect()->back()->with('error', 'Failed to send order to Steadfast. ' . ($response['message'] ?? 'Please try again.'));
    }

    public function exportCsv(Request $request)
    {
        [$query] = $this->filteredQuery($request);

        $orders = $query->with('deliveryCharge')->latest('created_at')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="orders-export.csv"',
        ];

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'customer_name', 'customer_phone', 'customer_address', 'payment_method', 'bkash_trx_id', 'bkash_sender_last4', 'subtotal', 'discount_amount', 'coupon_code', 'delivery_charge', 'total_amount', 'status', 'delivery_zone', 'is_sent_to_steadfast', 'steadfast_consignment_id', 'is_notification_sent', 'created_at']);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->id,
                    $order->customer_name,
                    $order->customer_phone,
                    $order->customer_address,
                    $order->payment_method,
                    $order->bkash_trx_id,
                    $order->bkash_sender_last4,
                    $order->subtotal,
                    $order->discount_amount,
                    $order->coupon_code,
                    $order->delivery_charge_amount,
                    $order->total_amount,
                    $order->status,
                    $order->deliveryCharge?->zone ?? '',
                    $order->is_sent_to_steadfast ? 1 : 0,
                    $order->steadfast_consignment_id,
                    $order->is_notification_sent ? 1 : 0,
                    $order->created_at,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);

        if (!$header) {
            fclose($handle);
            return redirect()->back()->with('error', 'Invalid CSV file.');
        }

        $header = array_map('trim', $header);
        $imported = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);

            if (empty($data['customer_name']) || empty($data['customer_phone'])) {
                $errors[] = 'Row missing customer_name or customer_phone, skipped.';
                continue;
            }

            try {
                Order::create([
                    'customer_name' => $data['customer_name'],
                    'customer_phone' => $data['customer_phone'],
                    'customer_address' => $data['customer_address'] ?? '',
                    'payment_method' => $data['payment_method'] ?? 'Cash on Delivery',
                    'bkash_trx_id' => $data['bkash_trx_id'] ?? null,
                    'total_amount' => $data['total_amount'] ?? 0,
                    'total' => $data['total_amount'] ?? 0,
                    'status' => $data['status'] ?? 'processing',
                    'is_sent_to_steadfast' => filter_var($data['is_sent_to_steadfast'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'is_notification_sent' => filter_var($data['is_notification_sent'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ]);

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Error importing order '{$data['customer_name']}': " . $e->getMessage();
            }
        }

        fclose($handle);

        $message = "Imported {$imported} orders successfully.";
        if (!empty($errors)) {
            $message .= ' ' . implode(' ', array_slice($errors, 0, 5));
        }

        return redirect()->route('admin.orders.index')->with('success', $message);
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', "Order #{$order->id} deleted successfully.");
    }

    /**
     * Build the filtered order query used by both the list and the CSV export.
     *
     * @return array{0: \Illuminate\Database\Eloquent\Builder, 1: array{key: string, label: string, from: ?string, to: ?string}}
     */
    private function filteredQuery(Request $request, bool $withStatus = true)
    {
        $query = Order::query();

        $period = $this->resolvePeriod($request);

        if ($period['from']) {
            $query->whereDate('created_at', '>=', $period['from']);
        }
        if ($period['to']) {
            $query->whereDate('created_at', '<=', $period['to']);
        }

        if ($withStatus && $request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('customer_address', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        }

        if ($request->filled('payment')) {
            $query->where('payment_method', $request->input('payment'));
        }

        return [$query, $period];
    }

    /**
     * Work out which time window the request is asking for.
     *
     * @return array{key: string, label: string, from: ?string, to: ?string}
     */
    private function resolvePeriod(Request $request): array
    {
        $now = Carbon::now();

        $range = $request->input('range');
        $custom = $request->filled('from') || $request->filled('to');

        if ($custom) {
            return [
                'key'   => 'custom',
                'label' => 'Custom Range',
                'from'  => $request->input('from'),
                'to'    => $request->input('to'),
            ];
        }

        if (!array_key_exists($range, self::PERIODS) || $range === 'custom') {
            $range = 'today';
        }

        [$from, $to] = match ($range) {
            'yesterday'  => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
            '7days'      => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            '30days'     => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfDay()],
            'last_month' => [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()],
            'all'        => [null, null],
            default      => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
        };

        return [
            'key'   => $range,
            'label' => self::PERIODS[$range],
            'from'  => $from?->toDateString(),
            'to'    => $to?->toDateString(),
        ];
    }
}
