<?php

namespace App\Http\Controllers\V1\Agency;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Ewallet;
use Illuminate\Http\Request;


class EwalletController extends Controller
{
    public function login(Request $request)
    {
        $agency = new Ewallet();
        return $agency->login();
    }
}
