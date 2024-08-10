<?php

namespace App\Http\Controllers\V1\Customer;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use App\Models\UsersBasket;
use App\Models\UsersInvoice;
use App\Models\UsersInvoicesProduct;
use App\Models\VendorProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class InvoiceController extends Controller
{
    public function preCreateInvoice()
    {
        try {
            $userId = Auth::id();

            $baskets = UsersBasket::query()
                ->with(['vendorProduct' => function ($q) use ($userId){
                    $q->with(['vendors_products_shipping' => function ($q) {
                        $q->join('shipping', 'shipping.id', '=', 'vendors_products_shipping.shipping_id')
                            ->select('vendors_products_shipping.id as id', 'vendors_products_shipping.vendor_product_id',
                                'shipping.by');
                    }])
                        ->join('products', 'products.id','=', 'vendors_products.product_id')
                        ->join('colors_sub_categories', 'colors_sub_categories.id','=', 'vendors_products.color_id')
                        ->select('vendors_products.id', 'vendor_id', 'product_id', 'vendors_products.price', 'vendors_products.free_delivery',
                            'products.avatar_link_l', 'products.title',
                            'colors_sub_categories.name as color_name', 'colors_sub_categories.code as color_code',
                        );
                }])
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
            $amount = $basket->vendorProduct->price;
            $sum += $amount;
        }

        return $sum;
    }

    public function createInvoice(Request $request)
    {
        try {
            $items = collect($request->all());
//            dd($items);
            $userId = Auth::id();

            // Check address
            $selectedAddresses = array_unique($items->pluck('user_address_id')->toArray());
            $addresses = UserAddress::query()->where('user_id', $userId)->whereIn('id', $selectedAddresses)->pluck('id')->toArray();
            if (!haveSameValues($selectedAddresses, $addresses)) {
                return ApiResponse::Json(400, 'آدرس وارد شده اشتباه است.', [],400);
            }

            // Check Basket
            $vendor_products_id = $items->pluck('vendor_product_id')->toArray();
            $baskets = UsersBasket::query()->where('user_id', $userId)->pluck('vendor_product_id')->toArray();
            if (!haveSameValues($vendor_products_id, $baskets) || count($baskets) != count($vendor_products_id)) {
                return ApiResponse::Json(400, 'عدم مطابقت با سبد خرید.', [], 400);
            }


            DB::beginTransaction();

            $userInvoice = UsersInvoice::query()->create([
                'user_id' => $userId,
                'tracking_code' => rand(1111111111, 9999999999),
            ]);

            $vendor_products = VendorProduct::query()->whereIn('id', $vendor_products_id)->get();

            $insert = [];
//            dd($items);
            foreach ($items as $item)
            {
                $vendor_product = $vendor_products->where('id', $item['vendor_product_id'])->first();
//                dd($vendor_product);
                $insert[] = [
                    'invoice_id'                   => $userInvoice->id,
                    'user_id'                      => $userId,
                    'vendor_product_id'            => $vendor_product->id,
                    'origin_price'                 => $vendor_product->price,
                    'paid_price'                   => $vendor_product->price,// TODO
                    'vendors_products_shipping_id' => $item['vendors_products_shipping_id'],// TODO check
                    'deliver_date_from'            => $item['deliver_date_from'],// TODO
                    'deliver_date_to'              => $item['deliver_date_from'],// TODO
                    'delivery_price'               => 50000,// TODO
                    'delivered_by_user_id'         => $userId,
                    'user_address_id'              => $item['user_address_id'],
                ];

            }

            UsersInvoicesProduct::query()->insert($insert);

            // TODO empty the basket
            DB::commit();

            return ApiResponse::Json(200,'عملیات با موفقیت انجام شد.', [],200);

        } catch (\Exception $exception) {

            DB::rollBack();
dd($exception->getMessage());
        }
    }
}
