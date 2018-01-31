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


	Route::resource('tasks', 'TaskController');

	/**
	 * Tasks Files Routes
	 */
	Route::post('tasks/{task}/files', 'FileController@uploads');

	Route::delete('tasks/{task}/files/{file}', 'FileController@detach');


	/**
	 * Users Routes
	 */
	Route::post('my/avatar', 'UserController@changeAvatar');

	Route::post('my/info', 'UserController@changeInfo');

	Route::get('find/user', 'UserController@search');


	/**
	 * Invitations Routes
	 */
	Route::get('invite/{user}/to/{task}', 'InvitationController@inviteToWatchPrivateTask');

	Route::get('accept/{invitation}', 'InvitationController@acceptInvitation');

	Route::get('deny/{invitation}', 'InvitationController@denyInvitation');

	Route::get('my/invitations', 'InvitationController@myInvitations');


	/**
	 * Watches Routes
	 */

	Route::get('watch/{task}', 'WatchController@watch');
});
