<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth
Route::prefix('auth')->group(function () {
    Route::post('/otp/request', 'AuthController@otpRequest');
    Route::post('/otp/verify', 'AuthController@otpVerification');
    Route::get('/get-user', 'AuthController@getUser')->middleware('auth:api');
});

// Basket
Route::prefix('basket')->middleware('auth:api')->group(function () {
    Route::post('/add', 'BasketController@addToBasket');
    Route::post('/remove', 'BasketController@removeFromBasket');
    Route::get('/list', 'BasketController@basketList');
    Route::get('/pre-create-invoice', 'BasketController@preCreateInvoice');

});

// Profile
Route::prefix('profile')->middleware('auth:api')->group(function () {
    Route::post('/address/add', 'UserController@addAddress');
    Route::post('/address/{id}/edit', 'UserController@editAddress');
    Route::post('/address/{id}/change-selected', 'UserController@changeSelectedAddress');
    Route::get('/address/list', 'UserController@listAddress');
});
