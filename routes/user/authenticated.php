<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\TaxRateController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('auth/logout', [AuthController::class, 'logout']);

Route::prefix('user')->group(function () {
    Route::apiResources([
        'branches' => BranchController::class,
        'customers' => CustomerController::class,
        'categories' => CategoryController::class,
        'tax-rates' => TaxRateController::class,
        'discounts' => DiscountController::class,
        'shifts' => ShiftController::class,
        'products' => ProductController::class,
        'product-variants' => ProductVariantController::class,
        'users' => UserController::class,
    ]);

    // Orders support index/show/store only — no update/destroy. A recorded
    // sale is never silently edited or deleted. See "Delete vs. deactivate" /
    // the Order section in SKILL.md.
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{uuid}', [OrderController::class, 'show']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::post('orders/{uuid}/print', [OrderController::class, 'print']);
    Route::post('orders/{uuid}/refund', [OrderController::class, 'refund']);
});
