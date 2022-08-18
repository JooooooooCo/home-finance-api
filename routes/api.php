<?php

use Illuminate\Support\Facades\Route;

Route::post('/register', 'App\Http\Controllers\Auth\UserAuthController@register');
Route::post('/login', 'App\Http\Controllers\Auth\UserAuthController@login');

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', 'App\Http\Controllers\Auth\UserAuthController@logout');
    Route::get('/me', 'App\Http\Controllers\Auth\UserAuthController@me');
});
