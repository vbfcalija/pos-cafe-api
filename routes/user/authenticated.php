<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\TaskController;
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
        'tasks' => TaskController::class,
    ]);

    // Orders support index/show/store only — no update/destroy. A recorded
    // sale is never silently edited or deleted. See "Delete vs. deactivate" /
    // the Order section in SKILL.md.
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{uuid}', [OrderController::class, 'show']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::post('orders/{uuid}/print', [OrderController::class, 'print']);
    Route::post('orders/{uuid}/receipt-pdf', [OrderController::class, 'downloadReceipt']);
    Route::post('orders/{uuid}/receipt-escpos', [OrderController::class, 'receiptEscPos']);
    Route::post('orders/{uuid}/refund', [OrderController::class, 'refund']);
    Route::patch('orders/{uuid}/payments/{paymentUuid}', [OrderController::class, 'updatePayment']);

    // Daily checklists: POST generates (or returns) a branch's checklist for
    // a day. There's no delete — a day's record is kept, like orders.
    Route::get('checklists', [ChecklistController::class, 'index']);
    Route::post('checklists', [ChecklistController::class, 'store']);
    Route::get('checklists/{uuid}', [ChecklistController::class, 'show']);
    Route::patch('checklists/{uuid}', [ChecklistController::class, 'update']);
    Route::patch('checklists/{uuid}/tasks/{taskCompletionUuid}', [ChecklistController::class, 'updateTask']);
    Route::post('checklists/{uuid}/users', [ChecklistController::class, 'addUser']);
    Route::put('checklists/{uuid}/users', [ChecklistController::class, 'syncUsers']);
    Route::delete('checklists/{uuid}/users/{checklistUserUuid}', [ChecklistController::class, 'removeUser']);

    // Activity log: read-only.
    Route::get('activity-logs', [ActivityLogController::class, 'index']);
    Route::get('activity-logs/{uuid}', [ActivityLogController::class, 'show']);
});
