<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

require __DIR__ . '/ApiRoutes/AuthRoutes.php';

require __DIR__ . '/ApiRoutes/UsersRoutes.php';

require __DIR__ . '/ApiRoutes/ClientsRoutes.php';

require __DIR__ . '/ApiRoutes/CategoriesRoutes.php';

require __DIR__ . '/ApiRoutes/ProductsRoutes.php';

require __DIR__ . '/ApiRoutes/OrderRoutes.php';

require __DIR__ . '/ApiRoutes/PaymentsRoutes.php';
