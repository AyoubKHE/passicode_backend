<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Payments\ChargilyPayWebhook;

Route::post(
    'chargilypay/webhook',
    ChargilyPayWebhook::class
)
    ->name('chargilypay.webhook_endpoint');