<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\ForgetPasswordController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\SendEmailVerificationLinkController;
use App\Http\Controllers\Auth\ReloadUserSessionController;


// tests made
Route::post(
    '/auth/register',
    RegisterController::class
)
    ->name('auth.register');


// tests made
Route::post(
    '/auth/login',
    LoginController::class
)
    ->name('auth.login');


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


// tests made
Route::post(
    '/auth/send-email-verification-link',
    SendEmailVerificationLinkController::class
)
    ->name('auth.send-email-verification-link');


// tests made
Route::get(
    '/auth/email-verification/{email_verification_token}',
    EmailVerificationController::class
)
    ->name('auth.email-verification');


// tests made
Route::post(
    '/auth/forget-password',
    ForgetPasswordController::class
)
    ->name('auth.forget-password');


// tests made
Route::post(
    '/auth/reset-password',
    PasswordResetController::class
)
    ->name('auth.reset-password');