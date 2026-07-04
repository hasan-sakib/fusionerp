<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard or login
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// ─── Authenticated & Verified Routes ─────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Module routes (each module appended as built) ─────────────────────────

    // Users
    Route::prefix('users')->name('users.')->middleware('can:users.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\UserController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\UserController::class, 'create'])->name('create')->middleware('can:users.create');
        Route::post('/', [\App\Http\Controllers\UserController::class, 'store'])->name('store')->middleware('can:users.create');
        Route::post('/{id}/restore', [\App\Http\Controllers\UserController::class, 'restore'])->name('restore');
        Route::post('/{user}/reset-password', [\App\Http\Controllers\UserController::class, 'resetPassword'])->name('reset-password')->middleware('can:users.edit');
        Route::get('/{user}', [\App\Http\Controllers\UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [\App\Http\Controllers\UserController::class, 'edit'])->name('edit')->middleware('can:users.edit');
        Route::patch('/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('update')->middleware('can:users.edit');
        Route::delete('/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('destroy')->middleware('can:users.delete');
    });

    // Roles
    Route::prefix('roles')->name('roles.')->middleware('can:roles.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\RoleController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\RoleController::class, 'create'])->name('create')->middleware('can:roles.create');
        Route::post('/', [\App\Http\Controllers\RoleController::class, 'store'])->name('store')->middleware('can:roles.create');
        Route::get('/{role}', [\App\Http\Controllers\RoleController::class, 'show'])->name('show');
        Route::get('/{role}/edit', [\App\Http\Controllers\RoleController::class, 'edit'])->name('edit')->middleware('can:roles.edit');
        Route::patch('/{role}', [\App\Http\Controllers\RoleController::class, 'update'])->name('update')->middleware('can:roles.edit');
        Route::delete('/{role}', [\App\Http\Controllers\RoleController::class, 'destroy'])->name('destroy')->middleware('can:roles.delete');
    });

    // Products
    Route::prefix('products')->name('products.')->middleware('can:products.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\ProductController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\ProductController::class, 'create'])->name('create')->middleware('can:products.create');
        Route::post('/', [\App\Http\Controllers\ProductController::class, 'store'])->name('store')->middleware('can:products.create');
        Route::get('/{product}', [\App\Http\Controllers\ProductController::class, 'show'])->name('show');
        Route::get('/{product}/edit', [\App\Http\Controllers\ProductController::class, 'edit'])->name('edit')->middleware('can:products.edit');
        Route::patch('/{product}', [\App\Http\Controllers\ProductController::class, 'update'])->name('update')->middleware('can:products.edit');
        Route::delete('/{product}', [\App\Http\Controllers\ProductController::class, 'destroy'])->name('destroy')->middleware('can:products.delete');
    });

    // Categories
    Route::prefix('categories')->name('categories.')->middleware('can:categories.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\CategoryController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\CategoryController::class, 'create'])->name('create')->middleware('can:categories.create');
        Route::post('/', [\App\Http\Controllers\CategoryController::class, 'store'])->name('store')->middleware('can:categories.create');
        Route::get('/{category}', [\App\Http\Controllers\CategoryController::class, 'show'])->name('show');
        Route::get('/{category}/edit', [\App\Http\Controllers\CategoryController::class, 'edit'])->name('edit')->middleware('can:categories.edit');
        Route::patch('/{category}', [\App\Http\Controllers\CategoryController::class, 'update'])->name('update')->middleware('can:categories.edit');
        Route::delete('/{category}', [\App\Http\Controllers\CategoryController::class, 'destroy'])->name('destroy')->middleware('can:categories.delete');
    });

    // Inventory
    Route::prefix('inventory')->name('inventory.')->middleware('can:inventory.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\InventoryController::class, 'index'])->name('index');
        Route::get('/movements', [\App\Http\Controllers\InventoryController::class, 'movements'])->name('movements');
        Route::get('/{product}', [\App\Http\Controllers\InventoryController::class, 'show'])->name('show');
        Route::post('/{product}/adjust', [\App\Http\Controllers\InventoryController::class, 'adjust'])->name('adjust')->middleware('can:inventory.adjust');
    });

    // Products restore
    Route::post('/products/{id}/restore', [\App\Http\Controllers\ProductController::class, 'restore'])->name('products.restore')->middleware(['auth', 'verified']);

    // Orders
    Route::prefix('orders')->name('orders.')->middleware('can:orders.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\OrderController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\OrderController::class, 'create'])->name('create')->middleware('can:orders.create');
        Route::post('/', [\App\Http\Controllers\OrderController::class, 'store'])->name('store')->middleware('can:orders.create');
        Route::get('/{order}', [\App\Http\Controllers\OrderController::class, 'show'])->name('show');
        Route::get('/{order}/edit', [\App\Http\Controllers\OrderController::class, 'edit'])->name('edit')->middleware('can:orders.edit');
        Route::patch('/{order}', [\App\Http\Controllers\OrderController::class, 'update'])->name('update')->middleware('can:orders.edit');
        Route::delete('/{order}', [\App\Http\Controllers\OrderController::class, 'destroy'])->name('destroy')->middleware('can:orders.delete');
        Route::patch('/{order}/status', [\App\Http\Controllers\OrderController::class, 'updateStatus'])->name('status');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->middleware('can:reports.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReportController::class, 'index'])->name('index');
        Route::get('/sales', [\App\Http\Controllers\ReportController::class, 'sales'])->name('sales');
        Route::get('/inventory', [\App\Http\Controllers\ReportController::class, 'inventory'])->name('inventory');
        Route::get('/export/{type}', [\App\Http\Controllers\ReportController::class, 'export'])->name('export')->middleware('can:reports.export');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->middleware('can:settings.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\SettingController::class, 'index'])->name('index');
        Route::patch('/', [\App\Http\Controllers\SettingController::class, 'update'])->name('update')->middleware('can:settings.edit');
    });
});

require __DIR__.'/auth.php';
