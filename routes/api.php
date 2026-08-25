<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Public — no auth required.
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

// Everything a logged-in cashier/staff member can do lives in
// routes/user/authenticated.php, gated behind auth:sanctum here — matching
// the user's Obiyen project (routes/api.php stays public-only; the bulk of
// the API is required in from routes/user/authenticated.php inside this
// middleware group).
Route::middleware('auth:sanctum')->group(function () {
    require 'user/authenticated.php';
});
