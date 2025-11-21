<?php

namespace App\Http\Controllers;

use App\Repositories\CustomerRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use App\Repositories\RefundRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    protected $orderRepo;

    protected $customerRepo;

    protected $orderItemRepo;

    protected $refundRepo;

    public function __construct(OrderRepository $orderRepo, CustomerRepository $customerRepo, OrderItemRepository $orderItemRepo, RefundRepository $refundRepo)
    {
        $this->orderRepo = $orderRepo;
        $this->customerRepo = $customerRepo;
        $this->orderItemRepo = $orderItemRepo;
        $this->refundRepo = $refundRepo;
    }

    /**
     * Home page — mostra overview + pedidos recentes
     */
    public function index()
    {
        $data = Cache::remember('dashboard:overview', 30, function () {
            return $this->overviewData();
        });

        $recent = Cache::remember('dashboard:recent_orders', 30, function () {
            $items = $this->orderRepo->getRecentOrders(10);
            if (is_object($items) && method_exists($items, 'toArray')) {
                return $items->toArray();
            }
            return is_array($items) ? $items : (method_exists($items, 'all') ? $items->all() : []);
        });

        $list = $this->orderItemRepo->getTopProducts(6);
        $topProducts = is_object($list) && method_exists($list, 'toArray') ? $list->toArray() : (array) $list;

        $listCities = $this->customerRepo->getTopCities(5);
        $topCities = is_object($listCities) && method_exists($listCities, 'toArray') ? $listCities->toArray() : (array) $listCities;

        $listSalesOvertime = $this->orderItemRepo->getSalesTrend(30);
        $salesOvertime = is_object($listSalesOvertime) && method_exists($listSalesOvertime, 'toArray') ? $listSalesOvertime->toArray() : (array) $listSalesOvertime;

        return view('dashboard.overview', [
            'data' => $data,
            'orders' => $recent,
            'recentOrders' => $recent,
            'topProducts' => $topProducts,
            'topCities' => $topCities,
            'salesOvertime' => $salesOvertime,
            'salesGeografic' => $this->customerRepo->getSalesGeograficDistribution(),
        ]);
    }

    /**
     * Web: tabela de pedidos (carrega todos os pedidos)
     */
    public function orders(Request $req)
    {
        $orders = $this->orderRepo->getOrders(null);

        if (is_object($orders) && method_exists($orders, 'toArray')) {
            $ordersArr = $orders->toArray();
            // collection->toArray may return indexed array of models
            if (isset($ordersArr['data']) && is_array($ordersArr['data'])) {
                $ordersArr = $ordersArr['data'];
            }
        } elseif (is_array($orders)) {
            $ordersArr = $orders;
        } elseif (method_exists($orders, 'all')) {
            $ordersArr = $orders->all();
        } else {
            $ordersArr = [];
        }

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;
        $items = collect($ordersArr);

        $paginator = new LengthAwarePaginator(
            $items->slice(($page - 1) * $perPage, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $req->url(), 'query' => $req->query()]
        );

        return view('dashboard.orders', [
            'orders' => $paginator,
        ]);
    }

    /**
     * Web: top products (view)
     */
    public function products(Request $req)
    {
        $limit = (int) $req->get('limit', 5);
        $list = $this->orderItemRepo->getTopProducts($limit);
        $products = is_object($list) && method_exists($list, 'toArray') ? $list->toArray() : (array) $list;

        return view('dashboard.products', ['products' => $products]);
    }

    /**
     * Helper: overview data (agora inclui BRL/USD, média por cliente, refund rate, etc.)
     */
    protected function overviewData(): array
    {
        $totalOrders = $this->orderRepo->count();

        // receita BRL (local_currency_amount)
        $totalRevenueBRL = (float) $this->orderRepo->sumRevenue();

        // tenta soma USD via repository, pode retornar null
        $totalRevenueUSD = null;
        if (method_exists($this->orderRepo, 'sumRevenueUsd')) {
            $totalRevenueUSD = $this->orderRepo->sumRevenueUsd();
        }

        $delivered = $this->orderRepo->countDelivered();
        $deliveryRate = $totalOrders ? round(($delivered / $totalOrders) * 100, 2) : 0;

        $uniqueCustomers = $this->orderRepo->countUniqueCustomers();
        $avgOrdersPerCustomer = $uniqueCustomers ? round($totalOrders / $uniqueCustomers, 2) : 0;

        $gross = $totalRevenueBRL;
        $refunds = $this->refundRepo->sumTotalAmount();
        $net = $gross - $refunds;

        $refundedOrders = $this->refundRepo->countRefundedOrders();
        $refundRate = $totalOrders ? round(($refundedOrders / $totalOrders) * 100, 2) : 0;

        $top = $this->orderItemRepo->getTopProducts(1);
        $topItem = (is_object($top) && method_exists($top, 'first')) ? $top->first() : (is_array($top) && count($top) ? (object) $top[0] : null);
        $topProduct = $topItem ? (method_exists($topItem, 'toArray') ? $topItem->toArray() : (array) $topItem) : null;

        return [
            'total_orders' => (int) $totalOrders,
            'total_revenue_brl' => (float) $totalRevenueBRL,
            'total_revenue_usd' => $totalRevenueUSD !== null ? (float) $totalRevenueUSD : null,
            'delivered_count' => (int) $delivered,
            'delivery_rate' => (float) $deliveryRate,
            'unique_customers' => (int) $uniqueCustomers,
            'avg_orders_per_customer' => (float) $avgOrdersPerCustomer,
            'gross' => (float) $gross,
            'refunds' => (float) $refunds,
            'net' => (float) $net,
            'refund_rate' => (float) $refundRate,
            'top_product' => $topProduct,
        ];
    }
}
