<?php

use Illuminate\Support\Facades\Route;

Route::post('/user-register', 'App\Http\Controllers\UserAuthController@register');
Route::post('/user-login', 'App\Http\Controllers\UserAuthController@login');

Route::middleware('auth:api')->group(function () {
    Route::post('/user-logout', 'App\Http\Controllers\UserAuthController@logout');
    Route::get('/user-details', 'App\Http\Controllers\UserAuthController@details');

    Route::apiResource('/cost-center', 'App\Http\Controllers\CostCenterController');

    Route::middleware('verify.tenant.header')->group(function () {
    });
});
