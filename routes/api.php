<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FeedbackController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'Localization'], function () {

    Route::post('/send-code-by-email', [AuthController::class, 'send_code_by_email']);

    Route::get('/feedback/index', [FeedbackController::class, 'index']);

    Route::group(['middleware' => 'guest:api', 'prefix' => 'auth'], function () {

        Route::post('/register/step-1', [AuthController::class, 'create_user']);

        Route::post('/register/step-2', [AuthController::class, 'verify_emails']);

        //step 3 requires the user to be authenticated

        Route::post('/login/two-factor-code', [AuthController::class, 'login_by_g2fa']);

        Route::post('/login/email-code', [AuthController::class, 'login_by_email_code']);

        Route::post('/login/security-code', [AuthController::class, 'login_by_security_code']);
    });

    Route::group(['middleware' => 'auth:api'], function () {

        Route::group(['prefix' => 'auth'], function () {

            Route::post('/register/step-3', [AuthController::class, 'verify_2fa']);

            Route::get('/refresh-2fa-secret', [AuthController::class, 'refresh_2fa_secret']);

            Route::get('/logout', [AuthController::class, 'logout']);

            Route::post('/grant-access', [AuthController::class, 'grant_access']);
        });

        Route::post('/feedback/create', [FeedbackController::class, 'create'])->middleware('role:premium');
    });
});

//coinbase 

// Route::post('/webhook', 'FeedbackController@testPost');
