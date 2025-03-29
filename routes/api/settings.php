<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::middleware('verify.tenant.header')->group(function () {
        Route::get('/settings/payment-type', 'App\Http\Controllers\Settings\PaymentTypeController@list');
        Route::post('/settings/payment-type', 'App\Http\Controllers\Settings\PaymentTypeController@create');
        Route::get('/settings/payment-type/{id}', 'App\Http\Controllers\Settings\PaymentTypeController@get');
        Route::put('/settings/payment-type/{id}', 'App\Http\Controllers\Settings\PaymentTypeController@update');
        Route::delete('/settings/payment-type/{id}', 'App\Http\Controllers\Settings\PaymentTypeController@delete');
    });
});
