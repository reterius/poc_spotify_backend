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

/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/







Route::get('/unauthorized', 'UserController@unauthorized');



Route::group([
], function () {
    Route::post('user/activate-email', 'UserController@activateEmail');
    Route::post('user/login', 'UserController@login')->name('user.login');
    Route::get('user', 'UserController@index');
    Route::post('user', 'UserController@store');
});
    

Route::group(['middleware' => ['CheckClientCredentials','auth:api']], function() {
    
    Route::post('user/logout', 'UserController@logout');
    
    Route::delete('user/{id}', 'UserController@destroy');
    
    Route::get('spotify', 'SpotifyController@index');
    

});

