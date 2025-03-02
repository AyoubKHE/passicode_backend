<?php

use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get("/test", function (Request $request) {
    // User::create([
    //     "first_name" => "Ayoub",
    //     "last_name" => "Kheyar",
    //     "email" => "a@a.com",
    //     "password" => Hash::make("111111"),
    //     "is_active" => "1",
    //     "role" => "Super Admin",
    //     "created_at" => now()
    // ]);

});
