<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::middleware('verify.tenant.header')->group(function () {
        Route::get('/settings/payment-type', 'App\Http\Controllers\Settings\PaymentTypeController@list');
        Route::post('/settings/payment-type', 'App\Http\Controllers\Settings\PaymentTypeController@create');
        Route::get('/settings/payment-type/{id}', 'App\Http\Controllers\Settings\PaymentTypeController@get');
        Route::put('/settings/payment-type/{id}', 'App\Http\Controllers\Settings\PaymentTypeController@update');
        Route::delete('/settings/payment-type/{id}', 'App\Http\Controllers\Settings\PaymentTypeController@delete');


        Route::get('/settings/classification', 'App\Http\Controllers\Settings\ClassificationController@list');
        Route::post('/settings/classification', 'App\Http\Controllers\Settings\ClassificationController@create');
        Route::get('/settings/classification/{id}', 'App\Http\Controllers\Settings\ClassificationController@get');
        Route::put('/settings/classification/{id}', 'App\Http\Controllers\Settings\ClassificationController@update');
        Route::delete('/settings/classification/{id}', 'App\Http\Controllers\Settings\ClassificationController@delete');

        Route::get('/settings/category', 'App\Http\Controllers\Settings\CategoryController@list');
        Route::post('/settings/category', 'App\Http\Controllers\Settings\CategoryController@create');
        Route::get('/settings/category/{id}', 'App\Http\Controllers\Settings\CategoryController@get');
        Route::put('/settings/category/{id}', 'App\Http\Controllers\Settings\CategoryController@update');
        Route::delete('/settings/category/{id}', 'App\Http\Controllers\Settings\CategoryController@delete');

        Route::get('/settings/sub-category', 'App\Http\Controllers\Settings\SubCategoryController@list');
        Route::post('/settings/sub-category', 'App\Http\Controllers\Settings\SubCategoryController@create');
        Route::get('/settings/sub-category/{id}', 'App\Http\Controllers\Settings\SubCategoryController@get');
        Route::put('/settings/sub-category/{id}', 'App\Http\Controllers\Settings\SubCategoryController@update');
        Route::delete('/settings/sub-category/{id}', 'App\Http\Controllers\Settings\SubCategoryController@delete');
    });
});
