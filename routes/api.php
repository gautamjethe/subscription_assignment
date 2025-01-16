<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RatingController;

Route::fallback(function () {
    return response()->json([
        'error' => 'Invalid API endpoint'
    ], 404);
});
//Product Routes
Route::apiResource('customers', CustomerController::class);
Route::apiResource('products', ProductController::class);


Route::post('/orders', [OrderController::class, 'placeOrder']);
Route::put('/orders/{order_id}', [OrderController::class, 'modifyOrder']);
Route::delete('/orders/{order_id}', [OrderController::class, 'cancelOrder']);
Route::get('/customers/{customer_id}/total', [OrderController::class, 'calculateTotal']);

//Subscription Routes

Route::post('/subscriptions', [SubscriptionController::class, 'create']);
Route::put('/subscriptions/{subscription_id}', [SubscriptionController::class, 'update']);
Route::delete('/subscriptions/{subscription_id}', [SubscriptionController::class, 'cancel']);
Route::get('/subscriptions/{customer_id}', [SubscriptionController::class, 'viewSubscriptions']);


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/products/{productId}/rate', [RatingController::class, 'rateProduct']); 
    Route::put('/products/{productId}/rating', [RatingController::class, 'updateRating']); 
    Route::get('/products/{productId}/rating', [RatingController::class, 'getProductRating']);

});