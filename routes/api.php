<?php

use Illuminate\Support\Facades\Route;

Route::post('/register', 'App\Http\Controllers\UserAuthController@register');
Route::post('/login', 'App\Http\Controllers\UserAuthController@login');

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', 'App\Http\Controllers\UserAuthController@logout');
    Route::get('/me', 'App\Http\Controllers\UserAuthController@me');
});
