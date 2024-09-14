<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth
Route::prefix('auth')->group(function () {
    Route::post('/otp/request', 'AuthController@otpRequest');
    Route::post('/otp/verify', 'AuthController@otpVerification');
    Route::get('/get-user', 'AuthController@getUser')->middleware('auth:api');
    Route::get('/logout', 'AuthController@logout')->middleware('auth:api');
});

// Profile
Route::prefix('profile')->middleware('auth:api')->group(function () {
    Route::post('/address/add', 'UserController@addAddress');
    Route::post('/address/{id}/edit', 'UserController@editAddress');
    Route::post('/address/{id}/change-selected', 'UserController@changeSelectedAddress');
    Route::get('/address/list', 'UserController@listAddress');
    Route::delete('/address/{addressId}', 'UserController@deleteAddress');

    Route::post('/details/', 'UserController@details');
});

// Basket
Route::prefix('basket')->middleware('auth:api')->group(function () {
    Route::post('/add', 'BasketController@addToBasket');
    Route::post('/remove', 'BasketController@removeFromBasket');
    Route::get('/list', 'BasketController@basketList');

});

// Invoice
Route::prefix('invoice')->middleware('auth:api')->group(function () {
    Route::get('/pre-create', 'InvoiceController@preCreateInvoice');
    Route::post('/create', 'InvoiceController@createInvoice');
    Route::post('/bill', 'InvoiceController@bill');
    Route::get('/redirect-to-gateway/{orderId}', 'InvoiceController@redirectToGateway');
    Route::get('/show/{id}', 'InvoiceController@show');
    Route::get('/product/list', 'InvoiceController@invoiceProductList');

});

// Notification
Route::prefix('notifications')->middleware('auth:api')->group(function () {
    Route::get('/get', 'NotificationController@getNotifications');
});
