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


Route::group(['middleware' => 'auth:api'], function()
{
	Route::get('logout', 'UserContrller@logout');


	Route::post('tasks/{task}/files', 'FileController@uploads');

	Route::delete('tasks/{task}/files/{file}', 'FileController@detach');


	Route::resource('tasks', 'TaskController');

	Route::post('my/avatar', 'UserController@changeAvatar');

	Route::post('my/info', 'UserController@changeInfo');

	Route::get('invite/{user}/to/{task}', 'UserController@inviteToWatchPrivateTask');

	Route::get('accept/{invitation}', 'UserController@acceptInvitation');
});
