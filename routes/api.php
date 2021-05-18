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

Route::post('test', function(Request $request) {
    echo "aaaabbbb";
    $bodyContent = $request->getContent();
    print_r($bodyContent);
    logfile($bodyContent);
});

Route::post('list-product2', function(Request $request) {
    $headerContent = $request->header();
    if (array_key_exists("vp6", $headerContent) && $headerContent['vp6'][0] == '12345')
    {
        $bodyContent = $request->getContent();
        print_r($bodyContent);
        logfile($bodyContent);
    } else {
        return abort(404);
    }
});

Route::post('/list-product/','ScraperController@getListProduct');
