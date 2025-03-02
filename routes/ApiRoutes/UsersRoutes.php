<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Users\GetMyAccountController;
// use App\Http\Controllers\Users\UpdateMyEmailController;
// use App\Http\Controllers\Users\UpdateMyAccountController;
// use App\Http\Controllers\Users\UpdateMyPasswordController;
// use App\Http\Controllers\Users\UpdateMyAccountImageController;


// tests made
Route::get(
    '/users/get-my-account',
    GetMyAccountController::class
)
    ->name('users.get-my-account')
    ->middleware('UsersJwtAuthentication');


// // tests made
// Route::put(
//     '/users/update-my-account',
//     UpdateMyAccountController::class
// )
//     ->name('users.update-my-account')
//     ->middleware('UsersJwtAuthentication');


// // tests made
// Route::put(
//     '/users/update-my-email',
//     UpdateMyEmailController::class
// )
//     ->name('users.update-my-email')
//     ->middleware('UsersJwtAuthentication');


// // tests made
// Route::put(
//     '/users/update-my-password',
//     UpdateMyPasswordController::class
// )
//     ->name('users.update-my-password')
//     ->middleware('UsersJwtAuthentication');


// // tests made
// Route::post(
//     '/users/update-my-account-image',
//     UpdateMyAccountImageController::class
// )
//     ->name('users.update-my-account-image')
//     ->middleware('UsersJwtAuthentication');