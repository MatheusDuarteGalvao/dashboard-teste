<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Refund;

class RefundRepository
{
    public function sumTotalAmount()
    {
        return Refund::sum('total_amount');
    }

    public function countRefundedOrders()
    {
        return Order::whereHas('refunds')->count();
    }
}
