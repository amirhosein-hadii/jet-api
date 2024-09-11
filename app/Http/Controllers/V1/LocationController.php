<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\LocationCities;
use App\Models\LocationProvince;
use App\Models\LocationRegion;


class LocationController extends Controller
{
    public function provinces()
    {
        $provinces = LocationProvince::query()->select('id', 'name')->get();
        return ApiResponse::Json(200,'', $provinces,200);
    }

    public function cities($provinceId)
    {
        $cities = LocationCities::query()
            ->where('province_id', $provinceId)
            ->select('id', 'name', 'longitude', 'latitude')
            ->get();

        return ApiResponse::Json(200,'', $cities,200);
    }

    public function regions($cityId)
    {
        $regions = LocationRegion::query()
            ->where('city_id', $cityId)
            ->select('id', 'name')
            ->get();

        return ApiResponse::Json(200,'', $regions,200);
    }

}
