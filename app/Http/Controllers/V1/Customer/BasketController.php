<?php

namespace App\Http\Controllers\V1\Customer;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\UsersBasket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BasketController extends Controller
{
    public function addToBasket(Request $request)
    {
        $validator = Validator::make($request->json()->all(), [
            'vendor_product_id' => 'required|integer',
            'next_purchase' => 'boolean|nullable',
        ]);

        if ($validator->fails()) {
            return ApiResponse::Json(400, $validator->errors()->first(),[],400);
        }

        UsersBasket::query()->insert([
            'user_id' => Auth::id(),
           'vendor_product_id' => $request->vendor_product_id,
            'next_purchase' => $request->next_purchase ?? 0,
        ]);

        return ApiResponse::Json(200,'عملیات با موفقیت انجام شد.', [],200);
    }

    public function removeFromBasket(Request $request)
    {
        $validator = Validator::make($request->json()->all(), [
            'vendor_product_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return ApiResponse::Json(400, $validator->errors()->first(),[],400);
        }

        $basketItem = UsersBasket::query()
            ->where('user_id', Auth::id())
            ->where('vendor_product_id', $request->vendor_product_id)
            ->first();

        if ($basketItem) {
            $basketItem->delete();
        }

        return ApiResponse::Json(200,'عملیات با موفقیت انجام شد.', [],200);
    }

    public function basketList()
    {
        try {

            $userId = Auth::id();
            $baskets = UsersBasket::query()
                ->with(['vendorProduct'])
                ->where('user_id', $userId)
                ->get();

            $basketSum = self::basketSum($baskets);

             $data = [
                 'total_price' => $basketSum,
                 'baskets' => $baskets
             ];

            return ApiResponse::Json(200,'', $data,200);

        } catch (\Exception $exception) {
            return ApiResponse::Json(400, $exception->getMessage(), [],400);
        }
    }

    public static function basketSum($baskets)
    {
        $sum = 0;
        foreach ($baskets as $basket) {
            $amount = $basket->vendorProducts->price;
            $sum += $amount;
        }

        return $sum;
    }

}
