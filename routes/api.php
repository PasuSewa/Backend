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

    Route::post('/send-code-by-email', 'AuthController@sendCodeByEmail');

    Route::get('/feedback/index', 'FeedbackController@index');

    Route::group(['middleware' => 'guest:api', 'prefix' => 'auth'], function () {

        //step 3 requires the user to be authenticated
        Route::post('/register/step-1', 'AuthController@create_user');

        Route::post('/register/step-2', 'AuthController@verify_emails');
    });

    Route::group(['middleware' => 'auth:api'], function () {

        Route::group(['prefix' => 'auth'], function () {
            Route::post('/register/step-3', 'AuthController@verify_2fa');

            Route::get('/refresh-2fa-secret', 'AuthController@refresh_2fa_secret');
        });

        Route::post('/feedback/create', 'FeedbackController@create')->middleware('role:premium');
    });
});

//coinbase 

// Route::post('/webhook', 'FeedbackController@testPost');
