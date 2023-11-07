<?php

use App\Http\Controllers\ApiClientsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', [ApiClientsController::class, 'postRegister']);
Route::post('login', [ApiClientsController::class, 'postLogin']);
Route::get('forgot/{id}', [ApiClientsController::class, 'getForgot']);
Route::get('otp/{id}/{email}', [ApiClientsController::class, 'getOtp']);
// fais passer le nouveau mot de passe en post et son id en get
Route::post('password', [ApiClientsController::class, 'postNewPassword']);
