<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    protected $casts = [
        'raw_payload' => 'array',
        'placed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(\App\Models\OrderItem::class, 'order_id');
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }
}
