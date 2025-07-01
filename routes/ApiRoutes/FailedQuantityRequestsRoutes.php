<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FailedQuantityRequests\ToggleSettledStatusController;
use App\Http\Controllers\FailedQuantityRequests\GetPaginatedFailedQuantityRequestsController;
use App\Http\Controllers\FailedQuantityRequests\GetPaginatedFailedQuantityRequestsByFilterController;



//tests made
Route::get(
    '/failed-quantity-requests/get-paginated-failed-quantity-requests',
    GetPaginatedFailedQuantityRequestsController::class
)
    ->name('failed-quantity-requests.get-paginated-failed-quantity-requests')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');


//! tests not made
Route::post(
    '/failed-quantity-requests/get-paginated-failed-quantity-requests-by-filter',
    GetPaginatedFailedQuantityRequestsByFilterController::class
)
    ->name('failed-quantity-requests.get-paginated-failed-quantity-requests-by-filter')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');


//! tests not made
Route::put(
    '/failed-quantity-requests/toggle-settled-status/{failed_quantity_request_id}',
    ToggleSettledStatusController::class
)
    ->name('failed-quantity-requests.toggle-settled-status')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsSuperAdmin');