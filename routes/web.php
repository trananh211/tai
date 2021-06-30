<?php

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', function () {
    return view('welcome');
})->middleware('auth');

Auth::routes();

Route::get('/home', 'UserController@index')->name('home');

/*Only member*/
Route::group(['middleware' => ['member']], function () {
    // verify data scrap
    Route::get('/view-scraper', 'AdminController@viewScraper');
    Route::post('/post-data-scrap-setup', 'AdminController@dataScrapSetup');
    Route::post('/save-data-scrap-setup', 'AdminController@saveDataScrapSetup');
    Route::get('/list-scraper', 'AdminController@getListScraper');

    // Connect Store, edit store
    Route::get('/list-stores', 'AdminController@listStore');
    Route::get('/new-template/{id}', 'AdminController@newTemplate');
    Route::get('/connect-woo', 'AdminController@connectWoo');
    Route::post('/get-woo-info', 'AdminController@getWooInfo');
});



