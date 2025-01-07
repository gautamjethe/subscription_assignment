<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SubscriptionController;

Route::fallback(function () {
    return response()->json([
        'error' => 'Invalid API endpoint'
    ], 404);
});
//Product Routes
Route::apiResource('customers', CustomerController::class);
Route::apiResource('products', ProductController::class);


Route::post('/orders', [OrderController::class, 'placeOrder'])->middleware(App\Http\Middleware\FiftyPercentMiddleware::class);
Route::put('/orders/{order_id}', [OrderController::class, 'modifyOrder'])->middleware(App\Http\Middleware\FiftyPercentMiddleware::class);
Route::delete('/orders/{order_id}', [OrderController::class, 'cancelOrder'])->middleware(App\Http\Middleware\FiftyPercentMiddleware::class);
Route::get('/customers/{customer_id}/total', [OrderController::class, 'calculateTotal'])->middleware(App\Http\Middleware\FiftyPercentMiddleware::class);

//Subscription Routes

Route::post('/subscriptions', [SubscriptionController::class, 'create'])->middleware(App\Http\Middleware\FiftyPercentMiddleware::class);
Route::put('/subscriptions/{subscription_id}', [SubscriptionController::class, 'update'])->middleware(App\Http\Middleware\FiftyPercentMiddleware::class);
Route::delete('/subscriptions/{subscription_id}', [SubscriptionController::class, 'cancel'])->middleware(App\Http\Middleware\FiftyPercentMiddleware::class);
Route::get('/subscriptions/{customer_id}', [SubscriptionController::class, 'viewSubscriptions'])->middleware(App\Http\Middleware\FiftyPercentMiddleware::class);
