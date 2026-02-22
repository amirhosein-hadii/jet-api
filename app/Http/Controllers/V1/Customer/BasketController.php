<?php

namespace App\Http\Controllers\V1\Customer;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\UsersBasket;
use App\Models\VendorProduct;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        try {
            $userId = Auth::id();

            // Check max purchasable num
            $maxPurchasableNum = VendorProduct::query()
                ->join('products', 'products.id', 'vendors_products.product_id')
                ->where('vendors_products.id', $request->vendor_product_id)
                ->value('products.max_purchasable_num');


            if (!is_null($maxPurchasableNum))
            {
                $num = UsersBasket::query()
                    ->where('user_id', $userId)
                    ->where('next_purchase', 0)
                    ->where('vendor_product_id', $request->vendor_product_id)
                    ->count();
                if ($num >= $maxPurchasableNum) {
                    return ApiResponse::Json(400, "حداکثر تعداد قابل خرید این محصول $maxPurchasableNum عدد می باشد.",[],400);
                }
            }

            UsersBasket::query()->insert([
                'user_id' => $userId,
                'vendor_product_id' => $request->vendor_product_id,
                'next_purchase' => $request->next_purchase ?? 0,
            ]);

            return ApiResponse::Json(200,'عملیات با موفقیت انجام شد.', [],200);

        } catch (\Exception $e) {
            return ApiResponse::Json(400, 'خطایی رخ داده است.',[],400);
        }
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
                ->join('vendors_products', 'vendors_products.id', 'users_basket.vendor_product_id')
                ->join('products', 'products.id','=', 'vendors_products.product_id')
                ->join('colors_sub_categories', 'colors_sub_categories.id','=', 'vendors_products.sub_color_id')
                ->groupBy('users_basket.vendor_product_id')
                ->select( 'vendor_id', 'product_id', 'vendor_product_id',
                    'products.avatar_link_l', 'products.title',
                    'colors_sub_categories.name as color_name', 'colors_sub_categories.code as color_code',
                    DB::raw('SUM(price) price'), DB::raw('COUNT(1) count')
                )
                ->where('user_id', $userId)
                ->get();

            return ApiResponse::Json(200,'', $baskets,200);

        } catch (\Exception $exception) {
            return ApiResponse::Json(400, $exception->getMessage(), [],400);
        }
    }

}
