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


// behpardakht
Route::get('behpardakht/create/trans/{orderId}', 'BehpardakhtController@createTransactions')->middleware('auth:api');
Route::post('behpardakht/callback/{orderId}', 'BehpardakhtController@callback');

//Route::get('test', function (){
//    $data = '{"RefId":"46A0C713F9210B71","ResCode":"0","SaleOrderId":"40","SaleReferenceId":"283625206361","CardHolderInfo":"A7B0716F6332B01800029A13D203C4A01E64CC0AAE2FC4C5808942EE442672DE","CardHolderPan":"610433******3582","FinalAmount":"10000"}';
////    dd(json_decode($data,true));
//    $dataArray = json_decode($data, true);
//
//    $request = new Request($dataArray);
//    $beh = new \App\Http\Controllers\V1\BehpardakhtController();
//    $beh->callback(40,$request);
//
//});
