<?php

use Illuminate\Support\Facades\Route;

Route::post('/user/register', 'App\Http\Controllers\UserAuthController@register');
Route::post('/user/login', 'App\Http\Controllers\UserAuthController@login');

Route::middleware('auth:api')->group(function () {
    Route::post('/user/logout', 'App\Http\Controllers\UserAuthController@logout');
    Route::get('/user/details', 'App\Http\Controllers\UserAuthController@details');
    Route::get('/settings/cost-center', 'App\Http\Controllers\CostCenterController@list');
    Route::post('/settings/cost-center', 'App\Http\Controllers\CostCenterController@create');
    Route::get('/settings/cost-center/{cost_center_id}', 'App\Http\Controllers\CostCenterController@get');
    Route::put('/settings/cost-center/{cost_center_id}', 'App\Http\Controllers\CostCenterController@update');
    Route::delete('/settings/cost-center/{cost_center_id}', 'App\Http\Controllers\CostCenterController@delete');

    Route::middleware('verify.tenant.header')->group(function () {
        Route::post('/user/current-cost-center', 'App\Http\Controllers\UserAuthController@currentCostCenter');
    });
});
