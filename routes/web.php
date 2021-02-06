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

    Route::group(['prefix' => 'company'], function()
    {
        Route::get('delete/{id}', 'AdminController@deleteCompany')
                ->name('delete_company')
                ->middleware('can:delete_companies');
        
        Route::post('create', 'AdminController@createCompany')
                ->name('create_company')
                ->middleware('can:create_companies');
        
        Route::post('update', 'AdminController@updateCompany')
                ->name('update_company')
                ->middleware('can:update_companies');
    });

    Route::group(['prefix' => 'suggestion'], function() 
    {
        Route::get('discard/{id}', 'AdminController@discardSuggestion')
                ->name('discard_suggestion')
                ->middleware('can:discard_suggestions');
        
        Route::get('publish/{id}', 'AdminController@publishSuggestion')
                ->name('publish_suggestion')
                ->middleware('can:publish_suggestions');
    });
    
    Route::group(['prefix' => 'rating'], function() 
    {
        Route::get('discard/{id}', 'AdminController@discardRating')
                ->name('discard_rating')
                ->middleware('can:discard_ratings');
        
        Route::get('publish/{id}', 'AdminController@publishRating')
                ->name('publish_rating')
                ->middleware('can:publish_ratings');
    });
});