<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\FeedbackController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['guest', 'Localization']], function () {

        Route::view('/', 'welcome')->name('welcome');

        Route::view('/login', 'welcome')->name('login');

        Route::post('/login', [AuthController::class, 'admin_login'])->name('login');
});

Route::group(['middleware' => ['auth', 'Localization', 'role:admin']], function () {

        Route::get('/dashboard', [AdminController::class, 'index'])->name('home');

        Route::get('/logout', 'AuthController@logout')->name('logout');

        Route::get('/statistics', [AdminController::class, 'show_statistics'])->name('statistics');

        Route::group(['prefix' => 'company'], function () {

                Route::get('/delete/{id}', [AdminController::class, 'delete_company'])
                        ->name('delete_company')
                        ->whereNumber('id')
                        ->middleware('can:delete_companies');

                Route::post('/create', [AdminController::class, 'create_company'])
                        ->name('create_company')
                        ->middleware('can:create_companies');

                Route::post('/update', [AdminController::class, 'update_company'])
                        ->name('update_company')
                        ->middleware('can:update_companies');
        });

        Route::group(['prefix' => 'feedback'], function () {

                Route::get('/discard/{id}', [FeedbackController::class, 'discard'])
                        ->name('discard_feedback')
                        ->whereNumber('id')
                        ->middleware('can:discard_feedback');

                Route::get('/publish/{id}', [FeedbackController::class, 'publish'])
                        ->name('publish_feedback')
                        ->whereNumber('id')
                        ->middleware('can:publish_feedbacks');
        });
});
