<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::middleware('verify.tenant.header')->group(function () {
        Route::get('/settings/payment-type', 'App\Http\Controllers\Settings\PaymentTypeController@list');
        Route::post('/settings/payment-type', 'App\Http\Controllers\Settings\PaymentTypeController@create');
        Route::get('/settings/payment-type/{id}', 'App\Http\Controllers\Settings\PaymentTypeController@get');
        Route::put('/settings/payment-type/{id}', 'App\Http\Controllers\Settings\PaymentTypeController@update');
        Route::delete('/settings/payment-type/{id}', 'App\Http\Controllers\Settings\PaymentTypeController@delete');

        Route::get('/settings/payment-status-type', 'App\Http\Controllers\Settings\PaymentStatusTypeController@list');
        Route::post('/settings/payment-status-type', 'App\Http\Controllers\Settings\PaymentStatusTypeController@create');
        Route::get('/settings/payment-status-type/{id}', 'App\Http\Controllers\Settings\PaymentStatusTypeController@get');
        Route::put('/settings/payment-status-type/{id}', 'App\Http\Controllers\Settings\PaymentStatusTypeController@update');
        Route::delete('/settings/payment-status-type/{id}', 'App\Http\Controllers\Settings\PaymentStatusTypeController@delete');

        Route::get('/settings/primary-category', 'App\Http\Controllers\Settings\PrimaryCategoryController@list');
        Route::post('/settings/primary-category', 'App\Http\Controllers\Settings\PrimaryCategoryController@create');
        Route::get('/settings/primary-category/{id}', 'App\Http\Controllers\Settings\PrimaryCategoryController@get');
        Route::put('/settings/primary-category/{id}', 'App\Http\Controllers\Settings\PrimaryCategoryController@update');
        Route::delete('/settings/primary-category/{id}', 'App\Http\Controllers\Settings\PrimaryCategoryController@delete');

        Route::get('/settings/secondary-category', 'App\Http\Controllers\Settings\SecondaryCategoryController@list');
        Route::post('/settings/secondary-category', 'App\Http\Controllers\Settings\SecondaryCategoryController@create');
        Route::get('/settings/secondary-category/{id}', 'App\Http\Controllers\Settings\SecondaryCategoryController@get');
        Route::put('/settings/secondary-category/{id}', 'App\Http\Controllers\Settings\SecondaryCategoryController@update');
        Route::delete('/settings/secondary-category/{id}', 'App\Http\Controllers\Settings\SecondaryCategoryController@delete');
    });
});
