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
Route::get('{user}/tasks', 'TaskController@viewOthersTasks')->name('users.tasks.view');
/*Route::get('{user}/tasks/{task}', 'TaskController@viewOthersTask');*/

Auth::routes();
