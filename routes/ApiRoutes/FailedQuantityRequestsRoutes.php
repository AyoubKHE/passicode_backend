<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FailedQuantityRequests\ToggleSettledStatusController;
use App\Http\Controllers\FailedQuantityRequests\GetPaginatedFailedQuantityRequestsController;



//tests no made
Route::get(
    '/failed-quantity-requests/get-paginated-failed-quantity-requests',
    GetPaginatedFailedQuantityRequestsController::class
)
    ->name('failed-quantity-requests.get-paginated-failed-quantity-requests')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');


// tests made
Route::put(
    '/failed-quantity-requests/toggle-settled-status/{failed_quantity_request_id}',
    ToggleSettledStatusController::class
)
    ->name('failed-quantity-requests.toggle-settled-status')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsSuperAdmin');