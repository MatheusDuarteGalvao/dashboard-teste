<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CustomerRepository
{
    public function getTopCities($limit = 10)
    {
        return Customer::select(
                'city',
                DB::raw('COUNT(*) as total')
            )
            ->join('orders', 'orders.customer_id', '=', 'customers.id')
            ->groupBy('city')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }

    public function getSalesGeograficDistribution()
    {
        return Customer::select(
                'state',
                DB::raw('SUM(orders.local_currency_amount) as total')
            )
            ->join('orders', 'orders.customer_id', '=', 'customers.id')
            ->groupBy('state')
            ->get();
    }
}
