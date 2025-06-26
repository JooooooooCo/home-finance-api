<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::middleware('verify.tenant.header')->group(function () {
        Route::get('/budget', 'App\Http\Controllers\Budget\BudgetController@get');
        Route::post('/budget', 'App\Http\Controllers\Budget\BudgetController@create');
        Route::put('/budget/{id}/categories', 'App\Http\Controllers\Budget\BudgetController@replaceCategories');
        Route::get('/budget/results', 'App\Http\Controllers\Budget\BudgetResultsController@getResults');
    });
});
