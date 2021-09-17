<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\AdminController;
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
                        ->middleware('can:delete_companies');

                Route::post('/create', [AdminController::class, 'create_company'])
                        ->name('create_company')
                        ->middleware('can:create_companies');

                Route::post('/update', [AdminController::class, 'update_company'])
                        ->name('update_company')
                        ->middleware('can:update_companies');
        });

        Route::group(['prefix' => 'suggestion'], function () {

                Route::get('/discard/{id}', [AdminController::class, 'discard_suggestion'])
                        ->name('discard_suggestion')
                        ->middleware('can:discard_suggestions');

                Route::get('/publish/{id}', [AdminController::class, 'publish_suggestion'])
                        ->name('publish_suggestion')
                        ->middleware('can:publish_suggestions');
        });

        Route::group(['prefix' => 'rating'], function () {

                Route::get('/discard/{id}', [AdminController::class, 'discard_rating'])
                        ->name('discard_rating')
                        ->middleware('can:discard_ratings');

                Route::get('/publish/{id}', [AdminController::class, 'publish_rating'])
                        ->name('publish_rating')
                        ->middleware('can:publish_ratings');
        });
});
