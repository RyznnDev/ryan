<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// import controllers
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\LocationController;

//login, sudah login -> dashboard
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// hanya untuk tamu (yang sudah login tidak bisa buka /login lagi)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// wajib login + cegah back history setelah logout
Route::middleware(['auth', 'prevent-back'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // produk
    Route::resource('/products', ProductController::class);

    // TRANSAKSI
    // stok masuk  -> stock-in.index / stock-in.create / stock-in.store
    Route::resource('stock-in', StockInController::class)->only(['index', 'create', 'store']);

    // stok keluar -> stock-out.index / stock-out.create / stock-out.store
    Route::resource('stock-out', StockOutController::class)->only(['index', 'create', 'store']);

    // transfer antar gudang -> transfers.index / transfers.create / transfers.store
    Route::resource('transfers', TransferController::class)->only(['index', 'create', 'store']);

    // LAPORAN
    Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');

    // ADMINISTRASI
    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('suppliers', SupplierController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('locations', LocationController::class)->only(['index', 'store', 'update', 'destroy']);
});