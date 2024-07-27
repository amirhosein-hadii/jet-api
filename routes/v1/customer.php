<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    Route::post('/otp/request', 'AuthController@otpRequest');
    Route::post('/otp/verify', 'AuthController@otpVerification');
    Route::get('/user', 'AuthController@getUser')->middleware('auth:api');

});
