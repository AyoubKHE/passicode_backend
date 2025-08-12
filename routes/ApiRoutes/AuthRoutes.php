<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LogoutController;

//! temp function for testing purposes
use App\Http\Controllers\Auth\TempLoginController;
//! temp function for testing purposes

use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\Auth\ReloadUserSessionController;


//! temp function for testing purposes 
Route::get(
    '/auth/temp-login/{role}',
    TempLoginController::class
);
//! temp function for testing purposes 


//! tests not made
Route::post(
    '/auth/google-login',
    GoogleLoginController::class
)
    ->name('auth.google-login');


// tests made
Route::get(
    '/auth/logout',
    LogoutController::class
)
    ->name('auth.logout')
    ->middleware('UsersJwtAuthentication');


Route::get(
    '/auth/reload-user-session',
    ReloadUserSessionController::class
)
    ->name('auth.reload-user-session');