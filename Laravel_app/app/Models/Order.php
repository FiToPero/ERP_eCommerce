<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'cart_id',
        'order_number',
        'status',
        'payment_status',
        'shipping_status',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_postcode',
        'shipping_country',
        'currency',
        'subtotal',
        'discount',
        'shipping_cost',
        'tax_amount',
        'total',
        'customer_notes',
        'internal_notes',
        'pending_at',
        'confirmed_at',
        'processing_at',
        'ready_to_ship_at',
        'shipped_at',
        'cancelled_at',
        'returned_at',
        'delivered_at',
        'refunded_at',
    ];
    protected $casts = [
        'pending_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'processing_at' => 'datetime',
        'ready_to_ship_at' => 'datetime',
        'shipped_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'returned_at' => 'datetime',
        'delivered_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
