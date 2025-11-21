<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index'])->name('home');

Route::get('/dashboard/orders', [DashboardController::class, 'orders'])->name('dashboard.orders');
Route::get('/dashboard/orders/delivered', [DashboardController::class, 'deliveredOrders'])->name('dashboard.delivered_orders');
Route::get('/dashboard/orders/refunded', [DashboardController::class, 'refundedOrders'])->name('dashboard.refunded_orders');
Route::get('/dashboard/orders/{order}', [DashboardController::class, 'orderDetails'])->name('dashboard.order_details');
