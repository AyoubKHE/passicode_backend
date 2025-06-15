<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\ToggleActiveController;

// use App\Http\Controllers\Users\GetMyAccountController;


// tests made
Route::put(
    '/users/toggle-active/{user_id}',
    ToggleActiveController::class
)
    ->name('users.toggle-active')
    ->middleware('UsersJwtAuthentication')
    ->middleware('IsSuperAdmin');

// tests made
// Route::get(
//     '/users/get-my-account',
//     GetMyAccountController::class
// )
//     ->name('users.get-my-account')
//     ->middleware('UsersJwtAuthentication');