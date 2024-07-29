<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/main-page', 'MainPageController@mainPage');

// Product
Route::get('/product/{id}/show', 'ProductController@show');
Route::get('/product/list', 'ProductController@list');
