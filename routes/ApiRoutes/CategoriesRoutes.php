<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Categories\GetCategoryByIdController;
use App\Http\Controllers\Categories\CategoryCreationController;
use App\Http\Controllers\Categories\DeleteCategoryByIdController;
use App\Http\Controllers\Categories\GetPaginatedCategoriesController;
use App\Http\Controllers\Categories\CategoryCreationPageDataController;
use App\Http\Controllers\FormationsCategories\UpdateFormationCategoryByIdController;
use App\Http\Controllers\FormationsCategories\UpdateFormationCategoryImageByIdController;
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


// // tests made
// Route::put(
//     '/categories/update-by-id/{formation_category_id}',
//     UpdateFormationCategoryByIdController::class
// )
//     ->name('categories.update-by-id')
//     ->middleware('UsersJwtAuthentication')
//     ->middleware('IsAdmin');


// // tests made
// Route::post(
//     '/categories/update-image-by-id/{formation_category_id}',
//     UpdateFormationCategoryImageByIdController::class
// )
//     ->name('categories.update-image-by-id')
//     ->middleware('UsersJwtAuthentication')
//     ->middleware('IsAdmin');


// tests made
Route::delete(
    '/categories/delete-by-id/{category_id}',
    DeleteCategoryByIdController::class
)
    ->name('categories.delete-by-id')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');