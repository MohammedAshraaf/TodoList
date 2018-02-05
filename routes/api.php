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
	Route::post('tasks/{task}/files', 'FileController@uploads')->name('uploadFile');

	Route::delete('tasks/{task}/files/{file}', 'FileController@detach')->name('deleteFile');


	/**
	 * Users Routes
	 */
	Route::post('my/avatar', 'UserController@changeAvatar')->name('user.avatar');

	Route::post('my/info', 'UserController@changeInfo')->name('user.info');

	Route::get('my/feed', 'UserController@newsFeed')->name('user.feed');

	Route::get('find/user', 'UserController@search')->name('user.find');




	/**
	 * Invitations Routes
	 */
	Route::get('invite/{user}/to/{task}', 'InvitationController@inviteToWatchPrivateTask')->name('invite.users');

	Route::get('accept/{invitation}', 'InvitationController@acceptInvitation')->name('invite.accept');

	Route::get('deny/{invitation}', 'InvitationController@denyInvitation')->name('invite.deny');

	Route::get('my/invitations', 'InvitationController@myInvitations')->name('invite.list');


	/**
	 * Watches Routes
	 */

	Route::get('watch/{task}', 'WatchController@watch')->name('watch.task');
});
