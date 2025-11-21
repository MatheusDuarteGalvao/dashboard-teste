<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index'])->name('home');

Route::get('/dashboard/orders', [DashboardController::class, 'orders'])->name('dashboard.orders');
