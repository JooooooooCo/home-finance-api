<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::middleware('verify.tenant.header')->group(function () {
        Route::get('/cashflow/transaction', 'App\Http\Controllers\CashFlow\TransactionController@list');
        Route::get('/cashflow/transaction/total-summary', 'App\Http\Controllers\CashFlow\TransactionController@getTotalSummary');
        Route::get('/cashflow/transaction/export', 'App\Http\Controllers\CashFlow\TransactionController@export');
        Route::post('/cashflow/transaction', 'App\Http\Controllers\CashFlow\TransactionController@create');
        Route::post('/cashflow/transaction/batch', 'App\Http\Controllers\CashFlow\TransactionController@createBatch');
        Route::get('/cashflow/transaction/{id}', 'App\Http\Controllers\CashFlow\TransactionController@get');
        Route::put('/cashflow/transaction/{id}', 'App\Http\Controllers\CashFlow\TransactionController@update');
        Route::delete('/cashflow/transaction/{id}', 'App\Http\Controllers\CashFlow\TransactionController@delete');
    });
});
