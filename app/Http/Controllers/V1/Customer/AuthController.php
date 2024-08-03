<?php

namespace App\Http\Controllers\V1\Customer;

use App\Http\Controllers\Controller;
use App\Jobs\SendSMSJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function otpRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|min:11|max:11',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 401);
        }

        $user = User::query()->firstOrCreate(
                ['cellphone' => $request->mobile],
                ['referral_code' => time()]
            );

        $user->otp_code = mt_rand(1111, 9999);
        $user->save();

        dispatch(new SendSMSJob($user->cellphone, $user->otp_code, 'JetMarketVerify'));

        return response()->json(['message' => 'کد با موفقیت ارسال شد.'], 200);
    }

    public function otpVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|min:11|max:11',
            'code' => 'required|string',
        ]);

        if ($validator->fails())
            return response()->json(['error' => $validator->errors()], 401);

        $user = User::query()->where('cellphone', $request->mobile)->first();

        if (!is_object($user))
            return response()->json(['error' => 'خطایی رخ داده است.'], 401);

        if ($request->code <> $user->otp_code)
            return response()->json(['error' => 'کد وارد شده اشتباه است.'], 401);

        if (!$userToken = JWTAuth::claims(['exp' => Carbon::now()->addYear()->timestamp])->fromUser($user))
            return response()->json(['error' => 'خطا در تولید کد ورود.'], 401);

        $user->update(['otp_code' => null]);

        $data = [
            'access_token' => $userToken,
            'token_type' => 'bearer'
        ];

        return response()->json(['message' => 'عملیات با موفقیت انجام شد.', 'data' => $data]);
    }


    public function getUser()
    {
        return response()->json(['message' => '', 'user' => Auth::user()]);
    }
}
