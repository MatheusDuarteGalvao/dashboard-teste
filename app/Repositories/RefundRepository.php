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

    public function getRefundReasons()
    {
        return Refund::select('reason', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
            ->groupBy('reason')
            ->orderByDesc('total')
            ->get();
    }

    public function getDeliveredVsRefunded()
    {
        return Order::select('fulfillment_status', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
            ->where('fulfillment_status', 'Fully Fulfilled')
            ->whereHas('refunds')
            ->groupBy('fulfillment_status')
            ->orderByDesc('total')
            ->get();
    }
}
