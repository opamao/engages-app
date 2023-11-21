<?php

use App\Http\Controllers\ApiClientsController;
use App\Http\Controllers\ApiInivtationsController;
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

// APIs authentification
Route::post('register', [ApiClientsController::class, 'postRegister']);
Route::post('login', [ApiClientsController::class, 'postLogin']);
Route::get('forgot/{id}', [ApiClientsController::class, 'getForgot']);
Route::get('otp/{id}/{email}', [ApiClientsController::class, 'getOtp']);
Route::post('password', [ApiClientsController::class, 'postNewPassword']);

// API mariage création
Route::post('mariage', [ApiInivtationsController::class,'createMariage']);
// liste des invitations
Route::get('invitation/{id}', [ApiInivtationsController::class,'getInvitation']);
Route::get('anniversaire/{id}', [ApiInivtationsController::class,'getAnniversaire']);
Route::get('bapteme/{id}', [ApiInivtationsController::class,'getBapteme']);
Route::get('naissance/{id}', [ApiInivtationsController::class,'getNaissance']);
Route::get('autre/{id}', [ApiInivtationsController::class,'getAutre']);

// Liste des invitations créées
Route::get('creermariage/{id}', [ApiInivtationsController::class,'getInvitationMariage']);
Route::get('creeranniversaire/{id}', [ApiInivtationsController::class,'getInvitationAnniversaire']);
Route::get('creerbapteme/{id}', [ApiInivtationsController::class,'getInvitationBapteme']);
Route::get('creernaissance/{id}', [ApiInivtationsController::class,'getInvitationNaissance']);
Route::get('creerautre/{id}', [ApiInivtationsController::class,'getInvitationAutres']);

// Integrer une invitation
Route::get('integrer/{code}/{id}/{type}', [ApiInivtationsController::class,'getIntegration']);
