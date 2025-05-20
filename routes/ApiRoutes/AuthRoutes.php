<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\ForgetPasswordController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ReloadUserSessionController;
use App\Http\Controllers\Auth\SendEmailVerificationLinkController;


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