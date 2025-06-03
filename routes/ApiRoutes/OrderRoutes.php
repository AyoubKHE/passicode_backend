<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Orders\OrderCreationController;
use App\Http\Controllers\Orders\GetMyOrdersController;

// tests made
Route::post(
    '/orders/create',
    OrderCreationController::class
)
    ->name('order.create')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsClient');


// tests made
Route::get(
    '/orders/get-my-orders',
    GetMyOrdersController::class
)
    ->name('orders.get-my-orders')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsClient');