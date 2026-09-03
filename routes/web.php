<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Auth\AuthController;
use App\Http\Controllers\Web\Dashboard\DashboardController;
use App\Http\Controllers\Web\Inventory\InventoryController;
use App\Http\Controllers\Web\Sale\SaleController;
use App\Http\Controllers\Web\Purchase\PurchaseController;
use App\Http\Controllers\Web\Report\ReportController;
use Spatie\Permission\Models\Role;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'role:SuperAdmin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

Route::middleware(['auth', 'role:SuperAdmin'])->group(function () {
    Route::get('/inventories', [InventoryController::class, 'index']);
});

Route::middleware(['auth', 'role:SuperAdmin|Sales'])->group(function () {
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
});

Route::middleware(['auth', 'role:SuperAdmin|Purchase'])->group(function () {
    Route::get('/purchases', [PurchaseController::class, 'index']);
});

Route::middleware(['auth', 'role:SuperAdmin|Manager'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index']);
});