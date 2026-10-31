<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbandonedOrder extends Model
{
    protected $fillable = [
        'session_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'cart_json',
        'subtotal',
    ];

    protected $casts = [
        'subtotal' => 'float',
    ];

    public function getCartAttribute(): array
    {
        return json_decode($this->cart_json ?: '[]', true);
    }
}
