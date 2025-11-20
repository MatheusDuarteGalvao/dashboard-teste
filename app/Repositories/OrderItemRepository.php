<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderItemRepository
{
    public function getTopProducts($limit = 5)
    {
        return OrderItem::select('name', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(local_currency_item_total_price) as revenue'))
            ->groupBy('name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }

    public function getSalesTrend($days = 30)
    {
        $start = now()->subDays($days - 1)->startOfDay();
        return Order::select(DB::raw('DATE(placed_at) as day'), DB::raw('SUM(local_currency_amount) as total'))
            ->where('placed_at', '>=', $start)
            ->groupBy('day')
            ->orderBy('day')
            ->get();
    }
}
