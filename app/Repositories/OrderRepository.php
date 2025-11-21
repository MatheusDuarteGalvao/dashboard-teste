<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Support\Facades\Schema;

class OrderRepository
{
    public function count()
    {
        return Order::count();
    }

    /**
     * Soma da receita em BRL (campo local_currency_amount).
     */
    public function sumRevenue()
    {
        // remove vírgulas na consulta se armazenado como string com vírgulas
        return (float) Order::query()
            ->selectRaw("SUM(REPLACE(local_currency_amount, ',', '')) as total")
            ->value('total') ?? 0.0;
    }

    /**
     * Tenta somar a receita em USD (campos comuns: total, total_usd, amount_usd).
     * Retorna null se não encontrar coluna aplicável.
     */
    public function sumRevenueUsd()
    {
        $keys = ['total', 'total_usd', 'amount_usd'];
        $table = (new Order)->getTable();

        foreach ($keys as $key) {
            if (Schema::hasColumn($table, $key)) {
                return (float) Order::query()
                    ->selectRaw("SUM(COALESCE({$key},0)) as total")
                    ->value('total') ?? 0.0;
            }
        }

        return null;
    }

    /**
     * Helper: verifica coluna no schema (simples, evita exceção se Schema não estiver disponível).
     */
    protected function schemaHasColumn(string $column): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn((new Order)->getTable(), $column);
        } catch (\Throwable $e) {
            return false;
        }
    }

    // Alias usado no código acima (facilita teste)
    private function schema_has_column(string $col)
    {
        return $this->schemaHasColumn($col);
    }

    public function countDelivered()
    {
        return Order::where('fulfillment_status', 'Fully Fulfilled')->count();
    }

    public function countUniqueCustomers()
    {
        return Order::distinct('customer_id')->count('customer_id');
    }

    public function getRecentOrders($limit = 50)
    {
        return Order::with('customer')
            ->orderBy('placed_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getOrders()
    {
        return Order::with('customer')->orderBy('placed_at', 'desc')->get();
    }

    public function getDeliveredVsRefunded()
    {
        return Order::selectRaw('
                SUM(CASE WHEN fulfillment_status = "Fully Fulfilled" THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN id IN (SELECT DISTINCT order_id FROM refunds) THEN 1 ELSE 0 END) as refunded
            ')
            ->first();
    }

    public function getRevenueByVariant()
    {
        return Order::selectRaw('SUM(local_currency_amount) as total_revenue, variant_id')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->groupBy('variant_id')
            ->orderBy('total_revenue', 'desc')
            ->get();
    }
}
