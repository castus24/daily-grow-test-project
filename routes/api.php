<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Client\ClientAddController;
use App\Http\Controllers\Api\Client\ClientAllController;
use App\Http\Controllers\Api\Client\ClientController;
use App\Http\Controllers\Api\Client\ClientDeleteController;
use App\Http\Controllers\Api\Client\ClientUpdateController;
use App\Http\Controllers\Api\EmailSetting\EmailSettingAddController;
use App\Http\Controllers\Api\EmailSetting\EmailSettingAllController;
use App\Http\Controllers\Api\EmailSetting\EmailSettingConnectionController;
use App\Http\Controllers\Api\EmailSetting\EmailSettingDeleteController;
use App\Http\Controllers\Api\EmailSetting\EmailSettingUpdateController;
use App\Http\Controllers\Api\Mailing\MailingController;
use App\Http\Controllers\Api\UserController;
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
Route::namespace('App\Http\Controllers\Api')
    ->group(function () {
        Route::post('register', [RegisterController::class, 'register']);
        Route::post('login', [LoginController::class, 'login']);
    });

Route::middleware('auth:sanctum')
    ->group(function () {
        Route::get('users', UserController::class);
        Route::prefix('clients')
            ->group(function () {
                Route::get('', ClientAllController::class);
                Route::post('', ClientAddController::class);
                Route::put('{client}', ClientUpdateController::class);
                Route::delete('{client}', ClientDeleteController::class);
                Route::post('import', [ClientController::class, 'import']);
            });

        Route::prefix('email-settings')
            ->group(function () {
                Route::get('', EmailSettingAllController::class);
                Route::post('', EmailSettingAddController::class);
                Route::put('{emailSetting}', EmailSettingUpdateController::class);
                Route::delete('{emailSetting}', EmailSettingDeleteController::class);
                Route::post('{emailSetting}/test', [EmailSettingConnectionController::class, 'testConnection']);
                Route::post('{emailSetting}/activate', [EmailSettingConnectionController::class, 'activate']);
            });


        Route::prefix('mailing')->group(function () {
            Route::apiResource('', MailingController::class);
            Route::get('/analytics', [MailingController::class, 'index']);
        });

    });


