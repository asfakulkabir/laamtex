<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #7c3aed, #ec4899); padding: 24px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 22px; }
        .body { padding: 24px; }
        .body h2 { margin-top: 0; color: #1e293b; font-size: 18px; }
        .info { margin-bottom: 20px; }
        .info p { margin: 4px 0; color: #475569; font-size: 14px; }
        .info strong { color: #1e293b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f1f5f9; color: #1e293b; font-size: 12px; text-transform: uppercase; padding: 8px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #475569; }
        .total { font-size: 18px; font-weight: bold; color: #1e293b; text-align: right; padding-top: 12px; }
        .footer { text-align: center; padding: 16px; color: #94a3b8; font-size: 12px; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Order Received</h1>
        </div>
        <div class="body">
            <h2>Order #{{ $order->id }}</h2>

            <div class="info">
                <p><strong>Customer:</strong> {{ $order->customer_name }}</p>
                <p><strong>Phone:</strong> {{ $order->customer_phone }}</p>
                <p><strong>Address:</strong> {{ $order->customer_address }}</p>
                <p><strong>Payment:</strong> {{ $order->payment_method }}</p>
                @if($order->bkash_trx_id)
                    <p><strong>bKash TrxID:</strong> {{ $order->bkash_trx_id }}</p>
                @endif
                @if($order->bkash_sender_last4)
                    <p><strong>bKash Sender Last 4:</strong> {{ $order->bkash_sender_last4 }}</p>
                @endif
                <p><strong>Date:</strong> {{ $order->created_at->format('d M Y, h:i A') }}</p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width:50px;">Item</th>
                        <th>Name</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        @php
                            $name  = is_array($item) ? ($item['name'] ?? 'N/A') : $item->product_name;
                            $qty   = is_array($item) ? ($item['quantity'] ?? 0) : $item->quantity;
                            $price = is_array($item) ? ($item['price'] ?? 0)    : $item->price;
                            $variation = is_array($item) ? ($item['variation_details'] ?? '') : ($item->variation_details ?? '');
                            $image = is_array($item) ? ($item['image'] ?? null) : null;
                        @endphp
                        <tr>
                            <td style="vertical-align:middle;">
                                @if($image)
                                    <img src="{{ asset('storage/' . $image) }}" alt="{{ $name }}" width="40" height="40" style="border-radius:4px;object-fit:cover;">
                                @else
                                    <span style="display:inline-block;width:40px;height:40px;background:#f1f5f9;border-radius:4px;"></span>
                                @endif
                            </td>
                            <td style="vertical-align:middle;">
                                {{ $name }}
                                @if($variation)
                                    <br><small style="color:#7c3aed;">{{ $variation }}</small>
                                @endif
                            </td>
                            <td style="vertical-align:middle;">{{ $qty }}</td>
                            <td style="vertical-align:middle;">৳{{ number_format($price, 2) }}</td>
                            <td style="vertical-align:middle;">৳{{ number_format($price * $qty, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="total">
                Total: ৳{{ number_format($order->total_amount, 2) }}
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} laamtex. All rights reserved.
        </div>
    </div>
</body>
</html>
