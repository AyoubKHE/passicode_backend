<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderItems\DeleteOrderItemController;
use App\Http\Controllers\OrderItems\OrderItemCreationController;

// tests made
Route::post(
    '/order-items/create',
    OrderItemCreationController::class
)
    ->name('order-items.create')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');


// tests made
Route::delete(
    '/order-items/delete/{order_id}/{product_id}',
    DeleteOrderItemController::class
)
    ->name('order-items.delete')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');