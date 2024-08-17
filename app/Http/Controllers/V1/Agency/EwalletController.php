<?php

namespace App\Http\Controllers\V1\Agency;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Ewallet;
use Illuminate\Http\Request;


class EwalletController extends Controller
{
    private $service;

    public function __construct()
    {
        $this->service = new Ewallet();
    }

    public function createUser(Request $request)
    {
        try {
            $res = $this->service->createUser('09361256776', 'legal');

            return $res;
        } catch (\Exception $e) {
            return ApiResponse::Json(400,$e->getMessage(), [],400);
        }
    }
}
