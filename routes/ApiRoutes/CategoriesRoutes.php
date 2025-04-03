<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Categories\getParentCategories;
use App\Http\Controllers\Categories\GetCategoryByIdController;
use App\Http\Controllers\Categories\CategoryCreationController;
use App\Http\Controllers\Categories\DeleteCategoryByIdController;
use App\Http\Controllers\Categories\UpdateImageController;
use App\Http\Controllers\Categories\UpdateParentCategoryController;
use App\Http\Controllers\Categories\GetPaginatedCategoriesController;
use App\Http\Controllers\Categories\UpdateCategoryBaseDataController;
use App\Http\Controllers\Categories\CategoryCreationPageDataController;
use App\Http\Controllers\FormationsCategories\GetPaginatedFormationsCategoriesByFilterController;

// tests made
Route::post(
    '/categories/create',
    CategoryCreationController::class

)
    ->name('categories.create')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');


// tests made
Route::get(
    '/categories/create-category-page-data',
    CategoryCreationPageDataController::class
)
    ->name('categories.create-category-page-data')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');


// tests made
Route::get(
    '/categories/get-parent-categories',
    getParentCategories::class
)
    ->name('categories.get-parent-categories')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');


// tests made
Route::get(
    '/categories/get-paginated-categories',
    GetPaginatedCategoriesController::class
)
    ->name('categories.get-paginated-categories')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');


// // tests made
// Route::post(
//     '/categories/get-paginated-categories-by-filter',
//     GetPaginatedFormationsCategoriesByFilterController::class
// )
//     ->name('categories.get-paginated-categories-by-filter')
//     ->middleware('UsersJwtAuthentication')
//     ->middleware('IsAdmin');


// tests made
Route::get(
    '/categories/get-by-id/{category_id}',
    GetCategoryByIdController::class
)
    ->name('categories.get-by-id')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');


// tests made
Route::put(
    '/categories/update-base-data/{category_id}',
    UpdateCategoryBaseDataController::class
)
    ->name('categories.update-base-data')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');


Route::put(
    '/categories/update-parent-category/{category_id}',
    UpdateParentCategoryController::class
)
    ->name('categories.update-parent-category')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');


// tests made
Route::post(
    '/categories/update-image/{category_id}',
    UpdateImageController::class
)
    ->name('categories.update-image')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');


// tests made
Route::delete(
    '/categories/delete-by-id/{category_id}',
    DeleteCategoryByIdController::class
)
    ->name('categories.delete-by-id')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');