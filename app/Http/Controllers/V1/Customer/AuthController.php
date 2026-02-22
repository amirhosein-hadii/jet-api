<?php

namespace App\Http\Controllers\V1\Customer;

use App\Events\UserRegistered;
use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Jobs\SendSMSJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function otpRequest(Request $request)
    {
        $validator = Validator::make($request->json()->all(), [
            'mobile' => 'required|min:11|max:11',
        ]);

        if ($validator->fails()) {
            return ApiResponse::Json(400, $validator->errors()->first(),[],400);
        }

        $user = User::query()->firstOrCreate(
                ['cellphone' => $request->mobile],
                ['referral_code' => rand(100000000000, 999999999999)]
            );

        $user->otp_code = mt_rand(1111, 9999);
        $user->save();

        dispatch(new SendSMSJob($user->cellphone, $user->otp_code, 'JetMarketVerify'));

        return ApiResponse::Json(200,'کد با موفقیت ارسال شد.',[],200);
    }

    public function otpVerification(Request $request)
    {
        $validator = Validator::make($request->json()->all(), [
            'mobile' => 'required|min:11|max:11',
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::Json(400, $validator->errors()->first(), [],400);
        }

        $user = User::query()->where('cellphone', $request->mobile)->first();

        if (!is_object($user)) {
            return ApiResponse::Json(400, 'خطایی رخ داده است.', [],400);
        }

        if ($request->code <> $user->otp_code) {
            return ApiResponse::Json(400, 'کد وارد شده اشتباه است.', [],400);
        }

        if (!$userToken = JWTAuth::claims(['exp' => Carbon::now()->addYear()->timestamp])->fromUser($user)) {
            return ApiResponse::Json(400, 'خطا در تولید کد ورود.', [],400);
        }

        $user->update(['otp_code' => null]);

        $data = [
            'access_token' => $userToken,
            'token_type' => 'bearer'
        ];

        if ( is_null($user->ewallet_user_id) ) {
            event(new UserRegistered($user));
        }

        return ApiResponse::Json(200,'عملیات با موفقیت انجام شد.', $data,200);
    }


    public function getUser()
    {
        return ApiResponse::Json(200,'عملیات با موفقیت انجام شد.', ['user' => Auth::user()],200);
    }

    public function logout()
    {
        auth()->logout();

        return ApiResponse::Json(200,'با موفقیت از حساب خود خارج شدید.', [],200);
    }
}
