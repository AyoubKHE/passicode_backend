<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Settings\ToggleAdminAvailabilityForBackorderController;


//! tests not made
Route::put(
    '/settings/toggle-admin-availability-for-backorder',
    ToggleAdminAvailabilityForBackorderController::class
)
    ->name('settings.toggle-admin-availability-for-backorder')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsAdmin');