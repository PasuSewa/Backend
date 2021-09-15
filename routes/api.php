<?php

use Illuminate\Support\Facades\Route;

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

    Route::post('/send-code-by-email', 'AuthController@send_code_by_email');

    Route::get('/feedback/index', 'FeedbackController@index');

    Route::group(['middleware' => 'guest:api', 'prefix' => 'auth'], function () {

        Route::post('/register/step-1', 'AuthController@create_user');

        Route::post('/register/step-2', 'AuthController@verify_emails');

        //step 3 requires the user to be authenticated

        Route::post('/login/two-factor-code', 'AuthController@login_by_g2fa');

        Route::post('/login/email-code', 'AuthController@login_by_email_code');

        Route::post('/login/security-code', 'AuthController@login_by_security_code');
    });

    Route::group(['middleware' => 'auth:api'], function () {

        Route::group(['prefix' => 'auth'], function () {
            Route::post('/register/step-3', 'AuthController@verify_2fa');

            Route::get('/refresh-2fa-secret', 'AuthController@refresh_2fa_secret');

            Route::get('/logout', 'AuthController@logout');
        });

        Route::post('/feedback/create', 'FeedbackController@create')->middleware('role:premium');
    });
});

//coinbase 

// Route::post('/webhook', 'FeedbackController@testPost');
