<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/main-page', 'MainPageController@mainPage');

// Product
Route::get('/product/{id}/show', 'ProductController@show');
Route::get('/product/list', 'ProductController@list');


// Location
Route::get('/location/provinces', 'LocationController@provinces');
Route::get('/location/{id}/cities', 'LocationController@cities');
Route::get('/location/{id}/regions', 'LocationController@regions');


// Order
//Route::get('behpardakht/callback/', '@provinces');
