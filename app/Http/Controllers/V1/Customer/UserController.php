<?php

namespace App\Http\Controllers\V1\Customer;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    public function addAddress(Request $request)
    {
        $validator = Validator::make($request->json()->all(), [
            'address' => 'required|string|max:255|min:10',
            'selected' => 'boolean|nullable',
        ]);

        if ($validator->fails()) {
            return ApiResponse::Json(400, $validator->errors()->first(),[],400);
        }

        UserAddress::query()->insert([
            'user_id' => Auth::id(),
            'address' => $request->address,
            'selected' => $request->selected == 1 ? 1 : 0,
        ]);

        return ApiResponse::Json(200,'عملیات با موفقیت انجام شد.', [],200);
    }

    public function editAddress($id, Request $request)
    {
        $validator = Validator::make($request->json()->all(), [
            'address' => 'required|string|max:255|min:10',
            'selected' => 'boolean|nullable',
        ]);

        if ($validator->fails()) {
            return ApiResponse::Json(400, $validator->errors()->first(),[],400);
        }

        $address = UserAddress::query()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$address) {
            return ApiResponse::Json(400, 'اطلاعات وارد شده اشتباه می باشد.',[],400);
        }

        $address->address = $request->address;
        $address->selected = $request->selected == 1 ? 1 : 0;
        $address->save();

        return ApiResponse::Json(200,'عملیات با موفقیت انجام شد.', [],200);
    }
}
