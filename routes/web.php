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

Route::group(['prefix' => 'api'], function(){

	Route::post('tasks/{task}/files', 'FileController@uploads');

	Route::delete('tasks/{task}/files/{file}', 'FileController@detach');

	Route::resource('tasks', 'TaskController');
});
