<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/list-product/','ScraperController@getListProduct');
Route::post('/list-product-images/','ScraperController@getListProductImages');
Route::post('/get-product-data/','ScraperController@getProductData');
Route::get('/test-product/','ScraperController@scrapProduct');

//new order woocommerce
Route::get('/test-new-order/{filename}','ApiController@testNewOrder');
Route::post('/new-order/','ApiController@newOrder');
