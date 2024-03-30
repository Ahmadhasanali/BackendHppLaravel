<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HppController;

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

Route::group(['middleware:api'], function () {
    Route::post('/add', [HppController::class, 'store']);
    Route::get('/get', [HppController::class, 'getAll']);
    Route::post('/update', [HppController::class, 'update']);
    Route::post('/remove', [HppController::class, 'destroy']);
});
