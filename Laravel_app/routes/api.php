<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;



// Public auth routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);
    Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/resend', [AuthController::class, 'resendVerification'])
        ->middleware('throttle:6,1');

    // Password recovery
    Route::post('forgot-password', [PasswordResetController::class, 'sendResetLink']);
    Route::post('reset-password',  [PasswordResetController::class, 'reset']);
});

// Protected routes (require Sanctum token)
Route::middleware('auth:api')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me',      [AuthController::class, 'me']);
});


// Public routes that do not require authentication
Route::group(['prefix' => 'products', 'middleware' => 'throttle:api'], function () {
    Route::get('/index', [ProductController::class, 'index']);
    //Route::get('/show/{id}', [ProductController::class, 'show']);
});