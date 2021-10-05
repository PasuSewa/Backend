<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CredentialController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\PaymentsController;
use App\Http\Controllers\Api\UserController;

Route::group(['middleware' => 'Localization'], function () {

    /**************************************************************************************************************** guests & authenticated routes */
    Route::post('/send-code-by-email', [AuthController::class, 'send_code_by_email']);

    Route::get('/feedback/index', [FeedbackController::class, 'index']);

    Route::get('/companies/index', [CredentialController::class, 'get_companies']);


    Route::group(['prefix' => 'coinbase-webhook'], function () {

        Route::post('/pending', [PaymentsController::class, 'crypto_order_received']);

        Route::post('/error', [PaymentsController::class, 'crypto_order_failed']);

        Route::post('/success', [PaymentsController::class, 'crypto_order_succeeded']);
    }); // *************************************************************** end of "/coinbase-webhook" routes

    /**************************************************************************************************************** only non-authenticated users */
    Route::group(['middleware' => 'guest:api', 'prefix' => 'auth'], function () {

        Route::group(['prefix' => 'register'], function () {

            Route::post('/step-1', [AuthController::class, 'create_user']);

            Route::post('/step-2', [AuthController::class, 'verify_emails']);

            //step 3 requires the user to be authenticated
        }); // *************************************************************** end of "/register" routes


        Route::group(['prefix' => 'login'], function () {

            Route::post('/two-factor-code', [AuthController::class, 'login_by_g2fa']);

            Route::post('/email-code', [AuthController::class, 'login_by_email_code']);

            Route::post('/security-code', [AuthController::class, 'login_by_security_code']);
        }); // *************************************************************** end of "/login" routes

    }); // ******************************************************************* end of "/auth" routes for non-authenticated users

    /**************************************************************************************************************** only authenticated users */
    Route::group(['middleware' => 'auth:api'], function () {

        Route::post('/feedback/create', [FeedbackController::class, 'create'])->middleware('role:premium');

        Route::post('/verify-paypal-payment', [PaymentsController::class, 'verify_paypal_payment']);

        Route::post('/start-payment-instance', [PaymentsController::class, 'start_payment_instance']);


        Route::group(['prefix' => 'auth'], function () {

            Route::post('/register/step-3', [AuthController::class, 'verify_2fa']);

            Route::get('/refresh-2fa-secret', [AuthController::class, 'refresh_2fa_secret']);

            Route::get('/logout', [AuthController::class, 'logout']);

            Route::post('/grant-access', [AuthController::class, 'grant_access']);

            Route::get('/renew-security-code', [AuthController::class, 'renew_security_code']);
        }); // *************************************************************** end of "/auth" routes


        Route::group(['prefix' => 'user'], function () {

            Route::put('/update', [UserController::class, 'update']);

            Route::get('/stop-premium', [UserController::class, 'stop_premium'])->middleware('role:premium');

            Route::put('/update-preferred-lang', [UserController::class, 'update_preferred_lang']);
        }); // *************************************************************** end of "/user" routes


        Route::group(['prefix' => 'credential'], function () {

            Route::post('/create', [CredentialController::class, 'create']);

            Route::get('/index', [CredentialController::class, 'index']);

            Route::get('/find/{credential_id}', [CredentialController::class, 'find']);

            Route::put('/update/{credential_id}', [CredentialController::class, 'update']);

            Route::get('/delete/{credential_id}', [CredentialController::class, 'delete']);

            Route::get('/get-recently-seen', [CredentialController::class, 'get_recently_seen']);
        }); // *************************************************************** end of "/credential" routes
    });
});
