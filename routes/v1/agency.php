<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth
Route::prefix('ewallet')->group(function () {
    Route::post('/login', 'EwalletController@login');

});


