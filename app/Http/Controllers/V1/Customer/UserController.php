<?php

namespace App\Http\Controllers\V1\Customer;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    public function addAddress(Request $request)
    {
        $validator = Validator::make($request->json()->all(), [
            'address' => 'required|string|max:255|min:10',
            'postal_code' => 'required|digits:10',
        ]);

        if ($validator->fails()) {
            return ApiResponse::Json(400, $validator->errors()->first(),[],400);
        }

        $userId = Auth::id();

        UserAddress::query()->where('user_id', $userId)->update(['selected' => 0]);

        UserAddress::query()->insert([
            'user_id'     => $userId,
            'address'     => $request->address,
            'postal_code' => $request->postal_code,
            'selected'    => 1,
           // 'location_area_id' => ''  TODO
        ]);

        return ApiResponse::Json(200,'عملیات با موفقیت انجام شد.', [],200);
    }

    public function editAddress($id, Request $request)
    {
        $validator = Validator::make($request->json()->all(), [
            'address' => 'required|string|max:255|min:10',
            'postal_code' => 'required|digits:10',
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

        $address->address          = $request->address;
        $address->postal_code      = $request->postal_code;
//        $address->location_area_id = $request->location_area_id; TODO
        $address->save();

        return ApiResponse::Json(200,'عملیات با موفقیت انجام شد.', [],200);
    }

    public function changeSelectedAddress($id)
    {
        try {
            DB::beginTransaction();

            $userId = Auth::id();
            UserAddress::query()->where('user_id', $userId)->update(['selected' => 0]);
            $update = UserAddress::query()->where('user_id', $userId)->where('id', $id)->update(['selected' => 1]);
            if (!$update) {
                throw new \Exception('اطلاعات وارد شده اشتباه است.');
            }

            DB::commit();

            return ApiResponse::Json(200,'عملیات با موفقیت انجام شد.', [],200);

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::Json(400, 'خطایی رخ داده است.', [],400);
        }
    }
}
