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
            'address'     => 'required|string|max:255|min:10',
            'postal_code' => 'required|digits:10',
            'city_id'     => 'required|exists:location_cities,id'
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
            'city_id'     => $request->city_id,
            'status'      => 'active'
        ]);

        return ApiResponse::Json(200,'عملیات با موفقیت انجام شد.', [],200);
    }

    public function editAddress($id, Request $request)
    {
        $validator = Validator::make($request->json()->all(), [
            'address'     => 'required|string|max:255|min:10',
            'postal_code' => 'required|digits:10',
            'city_id'     => 'required|exists:location_cities,id'
        ]);

        if ($validator->fails()) {
            return ApiResponse::Json(400, $validator->errors()->first(),[],400);
        }

        try {
            DB::beginTransaction();

            $userId = Auth::id();

            $inactiveAddress = UserAddress::query()
                ->where('id', $id)
                ->where('user_id', $userId)
                ->update(['status' => 'inactive']);

            if (!$inactiveAddress) {
                throw new \Exception('خطا در بروزرسانی آدرس قبلی');
            }


            UserAddress::query()->insert([
                'user_id'     => $userId,
                'address'     => $request->address,
                'postal_code' => $request->postal_code,
                'selected'    => 1,
                'city_id'     => $request->city_id,
                'status'      => 'active'
            ]);

            DB::commit();

            return ApiResponse::Json(200,'عملیات با موفقیت انجام شد.', [],200);

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::Json(400, 'اطلاعات وارد شده اشتباه می باشد.',[],400);
        }

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

    public function listAddress()
    {
        $addresses = UserAddress::query()
            ->join('location_cities','location_cities.id','=','users_addresses.city_id')
            ->join('location_provinces','location_provinces.id','=','location_cities.province_id')
            ->where('user_id', Auth::id())
            ->select('users_addresses.id', 'address', 'postal_code', 'selected',
                'location_cities.name as city_name', 'location_cities.id as city_id',
                'location_provinces.name as province_name' ,'location_provinces.id as province_id'
            )->orderBy('selected', 'desc')
            ->get();

        return ApiResponse::Json(200,'عملیات با موفقیت انجام شد.', $addresses,200);
    }
}
