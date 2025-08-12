<?php

use Illuminate\Support\Str;
use App\Models\Products\Product;
use App\Models\Products\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Products\GetProductByIdController;
use App\Http\Controllers\Products\ProductsCreationController;
use App\Http\Controllers\Products\DeleteProductByIdController;
use App\Http\Controllers\Products\GetUnsoldProductsController;

use App\Http\Controllers\Products\GetPaginatedProductsController;
use App\Http\Controllers\Products\UpdateProductBaseDataController;
use App\Http\Controllers\Products\GetPaginatedProductsByFilterController;

// tests made
Route::get(
    '/products/create-random-products',
    function () {
        DB::transaction(function () {

            for ($i = 1; $i < 500; $i++) {
                $code = strtoupper(Str::random(20));

                $code_start = substr($code, 0, 5);

                Product::create([
                    'category_id' => 3,
                    'code' => Crypt::encryptString($code),
                    'code_start' => $code_start,
                    'sold' => 0,
                    'status' => 'valid',
                    'expiration_date' => '9999-12-31',
                    'purchase_price' => 4000,
                    'supplier' => 'Supplier Name',
                    'created_at' => now(),
                    'updated_at' => null,
                ]);
            }

            $category = Category::where('id', 3)->first();

            $category->update([
                'quantity' => $category->quantity + 500,
            ]);
        });
    }

);

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