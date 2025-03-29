<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('/settings/cost-center', 'App\Http\Controllers\CostCenterController@list');
    Route::post('/settings/cost-center', 'App\Http\Controllers\CostCenterController@create');
    Route::get('/settings/cost-center/{id}', 'App\Http\Controllers\CostCenterController@get');
    Route::put('/settings/cost-center/{id}', 'App\Http\Controllers\CostCenterController@update');
    Route::delete('/settings/cost-center/{id}', 'App\Http\Controllers\CostCenterController@delete');
});
