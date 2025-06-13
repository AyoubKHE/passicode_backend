<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Orders\GetMyOrdersController;
use App\Http\Controllers\Orders\GetOrderByIdController;
use App\Http\Controllers\Orders\OrderCreationController;
use App\Http\Controllers\Orders\GetPaginatedOrdersController;
use App\Http\Controllers\Orders\OrdersFilterDialogDataController;
use App\Http\Controllers\Orders\GetPaginatedOrdersByFilterController;

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


// tests made
Route::get(
    '/orders/get-paginated-orders',
    GetPaginatedOrdersController::class
)
    ->name('orders.get-paginated-orders')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');


// tests made
Route::post(
    '/orders/get-paginated-orders-by-filter',
    GetPaginatedOrdersByFilterController::class
)
    ->name('orders.get-paginated-orders-by-filter')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');


// tests made
Route::get(
    '/orders/get-by-id/{order_id}',
    GetOrderByIdController::class
)
    ->name('orders.get-by-id')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');


//tests made
Route::get(
    '/orders/orders-filter-dialog-data',
    OrdersFilterDialogDataController::class
)
    ->name('orders.orders-filter-dialog-data')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');