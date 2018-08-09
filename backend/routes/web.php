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

Route::post('/api/login', 'Auth\LoginController@login');
Route::get('/api/vbot/qr', 'InfoController@qr');
Route::get('/api/vbot/status', 'InfoController@status');
Route::get('/api/vbot/test', 'InfoController@testMsg');
Route::get('/api/vbot/clear', 'InfoController@clearAuth');

Route::get('/api/vbot/info', 'InfoController@info');
Route::get('/api/vbot/contacts', 'InfoController@contacts');
Route::post('/api/vbot/send', 'InfoController@send');
Route::get('/api/vbot/status', 'InfoController@status');
Route::get('/api/vbot/groups', 'InfoController@groups');
Route::get('/api/group/user/list', 'InfoController@groupUser');

Route::get('/api/task/list', 'TaskController@list');
Route::post('/api/task', 'TaskController@save');
Route::delete('/api/task/{id}', 'TaskController@delete');

Route::get('/api/label/list', 'LabelController@list');
Route::post('/api/label', 'LabelController@save');
Route::delete('/api/label/{id}', 'LabelController@delete');

Route::get('/api/label/user/list', 'LabelUserController@list');
Route::post('/api/label/user', 'LabelUserController@save');
Route::delete('/api/label/user/{id}', 'LabelUserController@delete');


Route::get('/api/msg/list', 'MsgController@list');

//群发计划
Route::get('/api/plan', 'PlanController@listPlan');
Route::post('/api/plan', 'PlanController@create');
Route::post('/api/plan/count', 'PlanController@countNum');
Route::post('/api/plan/confirm', 'PlanController@confirm');
Route::delete('/api/plan/{id}', 'PlanController@cancel');
Route::get('/api/plan/province', 'PlanController@listProvince');













