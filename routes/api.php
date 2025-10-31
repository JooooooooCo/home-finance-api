<?php

use Illuminate\Support\Facades\Route;

Route::post('/user/register', 'App\Http\Controllers\UserAuthController@register');
Route::post('/user/login', 'App\Http\Controllers\UserAuthController@login');

Route::middleware('auth:api')->group(function () {
    Route::post('/user/logout', 'App\Http\Controllers\UserAuthController@logout');
    Route::get('/user/details', 'App\Http\Controllers\UserAuthController@details');

    Route::get('/cost-center', 'App\Http\Controllers\CostCenterController@list');
    Route::post('/cost-center', 'App\Http\Controllers\CostCenterController@create');
    Route::get('/cost-center/{id}', 'App\Http\Controllers\CostCenterController@get');
    Route::put('/cost-center/{id}', 'App\Http\Controllers\CostCenterController@update');
    Route::delete('/cost-center/{id}', 'App\Http\Controllers\CostCenterController@delete');
});

require __DIR__ . '/api/settings.php';
require __DIR__ . '/api/cashflow.php';
require __DIR__ . '/api/budget.php';