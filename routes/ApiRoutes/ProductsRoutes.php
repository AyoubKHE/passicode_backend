<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Products\GetProductByIdController;
use App\Http\Controllers\Products\ProductsCreationController;
use App\Http\Controllers\Products\DeleteProductByIdController;
use App\Http\Controllers\Products\GetUnsoldProductsController;
use App\Http\Controllers\Products\GetPaginatedProductsController;
use App\Http\Controllers\Products\UpdateProductBaseDataController;
use App\Http\Controllers\Products\GetPaginatedProductsByFilterController;


// tests made
Route::post(
    '/products/create',
    ProductsCreationController::class

)
    ->name('products.create')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');


// tests made
Route::get(
    '/products/get-paginated-products',
    GetPaginatedProductsController::class
)
    ->name('products.get-paginated-products')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');


// tests made
Route::post(
    '/products/get-paginated-products-by-filter',
    GetPaginatedProductsByFilterController::class
)
    ->name('products.get-paginated-products-by-filter')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');


// tests made
Route::get(
    '/products/get-unsold-products/{category_id}',
    GetUnsoldProductsController::class
)
    ->name('products.get-unsold-products')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');


// tests made
Route::get(
    '/products/get-by-id/{product_id}',
    GetProductByIdController::class
)
    ->name('products.get-by-id')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');


// tests made
Route::put(
    '/products/update-base-data/{product_id}',
    UpdateProductBaseDataController::class
)
    ->name('products.update-base-data')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');


// tests made
Route::delete(
    '/products/delete-by-id/{product_id}',
    DeleteProductByIdController::class
)
    ->name('products.delete-by-id')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');