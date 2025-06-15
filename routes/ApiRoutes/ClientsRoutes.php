<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Clients\GetPaginatedClientsController;


// tests made
Route::get(
    '/clients/get-paginated-clients',
    GetPaginatedClientsController::class
)
    ->name('clients.get-paginated-clients')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');