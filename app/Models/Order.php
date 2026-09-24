<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED    = 'shipped';
    const STATUS_DELIVERED  = 'delivered';
    const STATUS_CANCELLED  = 'cancelled';

    const STATUSES = [
        'processing' => '🔷 Processing',
        'shipped'    => '🚚 Shipped',
        'delivered'  => '✅ Delivered',
        'cancelled'  => '❌ Cancelled',
    ];

    protected $fillable = [
        'user_id',
        'items_json',
        'payment_method',
        'customer_name',
        'customer_phone',
        'customer_address',
        'delivery_charge_id',
        'bkash_trx_id',
        'bkash_sender_last4',
        'total_amount',
        'total',          // alias kept for backward compat in views
        'status',
        'is_sent_to_steadfast',
        'is_notification_sent',
        'steadfast_consignment_id',
    ];

    protected $casts = [
        'total_amount'           => 'integer',
        'is_sent_to_steadfast'   => 'boolean',
        'is_notification_sent'   => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryCharge()
    {
        return $this->belongsTo(DeliveryCharge::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Helpers
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getItemsAttribute(): array
    {
        return json_decode($this->items_json ?: '[]', true);
    }
}
