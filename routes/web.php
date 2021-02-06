<?php

use Illuminate\Support\Facades\Route;

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

Route::group(['middleware' => ['guest', 'Localization']], function()
{
    Route::view('/', 'welcome')->name('welcome');

    Route::view('/login', 'welcome')->name('login');

    Route::post('/login', 'AuthController@login')->name('login');
});

Route::group(['middleware' => ['auth', 'Localization', 'role:admin']], function()
{
    Route::get('dashboard', 'AdminController@index')->name('home');

    Route::get('logout', 'AuthController@logout')->name('logout');

    Route::view('statistics', 'statistics')->name('statistics');

    Route::group(['prefix' => 'companiy'], function()
    {
        Route::get('delete/{id}', 'AdminController@deleteCompany')->name('delete_company')->middleware('can:delete_companies');
    });
});