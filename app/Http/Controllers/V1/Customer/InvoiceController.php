<?php

namespace App\Http\Controllers\V1\Customer;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\UserAddress;
use App\Models\UsersBasket;
use App\Models\UsersInvoice;
use App\Models\UsersInvoicesProduct;
use App\Models\VendorProduct;
use App\Models\VendorsProductsShipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class InvoiceController extends Controller
{
    const SHIPPING_DATE_FROM = 5; // deliver after 5 days
    const SHIPPING_DATE_DILAY = 3; // deliver after 5 days

    const SHIPPING_DATE_DILAY_FOR_NOT_IN_SAME_CITY = 2;

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
                        ->join('colors_sub_categories', 'colors_sub_categories.id','=', 'vendors_products.sub_color_id')
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
        $validator = Validator::make($request->json()->all(), [
            '*.vendor_product_id'            => 'required|integer',
            '*.user_address_id'              => 'required|integer',
            '*.vendors_products_shipping_id' => 'required|integer',
        ], [
            '*.vendor_product_id.required' => 'انتخاب محصول اجباری است.',
            '*.user_address_id.required' => 'انتخاب آدرس برای هر محصول اجباری است.',
            '*.vendors_products_shipping_id.required' => 'انتخاب نحوه ارسال برای هر محصول اجباری است.',
        ]);

        if ($validator->fails()) {
            return ApiResponse::Json(400, $validator->errors()->first(),[],400);
        }

        try {
            $items = collect($request->all());
            $userId = Auth::id();

            // Check address
            $selectedAddresses = array_unique($items->pluck('user_address_id')->toArray());
            $addresses = UserAddress::query()->where('user_id', $userId)->whereIn('id', $selectedAddresses)->select('id', 'city_id')->get();
            if (!haveSameValues($selectedAddresses, $addresses->pluck('id')->toArray())) {
                return ApiResponse::Json(400, 'آدرس وارد شده اشتباه است.', [],400);
            }

            // Check Basket
            $vendor_products_id = $items->pluck('vendor_product_id')->toArray();
            $baskets = UsersBasket::query()->where('user_id', $userId)->pluck('vendor_product_id')->toArray();
            if (!haveSameValues($vendor_products_id, $baskets) || count($baskets) != count($vendor_products_id)) {
                return ApiResponse::Json(400, 'عدم مطابقت با سبد خرید.', [], 400);
            }

            // Check shipping
            $vpsi = $items->pluck('vendor_product_id', 'vendors_products_shipping_id')->toArray();
            $vendorProductsShipping = VendorsProductsShipping::query()->whereIn('vendor_product_id', $vendor_products_id)->pluck('vendor_product_id', 'id')->toArray();
            foreach ($vpsi as $key => $value) {
                if (!array_key_exists($key, $vendorProductsShipping) || $vendorProductsShipping[$key] !== $value) {
                    throw new \Exception('آدرس انتخاب شده صحیح نمی باشد.');
                }
            }

            DB::beginTransaction();

            $userInvoice = UsersInvoice::query()->create([
                'user_id'       => $userId,
                'status'        => 'waiting',
                'tracking_code' => rand(1111111111, 9999999999)
            ]);

            $vendor_products = VendorProduct::query()->with('vendor')->whereIn('id', $vendor_products_id)->get();

            $insert = [];
            $totalAmount = 0;
            foreach ($items as $item)
            {
                $vendor_product = $vendor_products->where('id', $item['vendor_product_id'])->first();

                $requestCityId = $addresses->where('id', $item['user_address_id'])->value('city_id');
                $at_same_city =  $vendor_product->vendor->city_id == $requestCityId;

                // Calculate delivery time
                $delivery_dilay_for_not_same_city = $at_same_city ? 0 : self::SHIPPING_DATE_DILAY_FOR_NOT_IN_SAME_CITY;
                $deliver_date_from = self::SHIPPING_DATE_FROM + $delivery_dilay_for_not_same_city;
                $deliver_date_to = $deliver_date_from + self::SHIPPING_DATE_DILAY;

                // Calculate shipping cost
                $shippingCost = self::getShippingCost($vendor_product->product->weight_size_level, $vendor_product->product->breakable);
                $delivery_price = $at_same_city ? $shippingCost : $shippingCost + 50000;

                $origin_price = $vendor_product->price + $delivery_price;

                $paid_price = $origin_price; // TODO after discount

                $totalAmount += $paid_price;

                $insert[] = [
                    'invoice_id'                   => $userInvoice->id,
                    'user_id'                      => $userId,
                    'vendor_product_id'            => $vendor_product->id,
                    'origin_price'                 => $origin_price,
                    'paid_price'                   => $paid_price,// TODO after discount
                    'vendors_products_shipping_id' => $item['vendors_products_shipping_id'],
                    'deliver_date_from'            => jalalianAddDays($deliver_date_from),
                    'deliver_date_to'              => jalalianAddDays($deliver_date_to),
                    'delivery_price'               => $delivery_price,
                    'delivered_by_user_id'         => $userId,
                    'user_address_id'              => $item['user_address_id'],
                ];

            }

            UsersInvoicesProduct::query()->insert($insert);

            Order::query()->create([
                'gateway_provider' => 'Mellat',
                'user_id'          => $userId,
                'invoice_id'       => $userInvoice->id,
                'type'             => 'purchase_payment',
                'amount'           => $totalAmount,
                'status'           => 'waiting',
            ]);

            UsersBasket::query()->where('user_id', $userId)->delete();

            DB::commit();

            return ApiResponse::Json(200,'عملیات با موفقیت انجام شد.', [],200);

        } catch (\Exception $exception) {

            DB::rollBack();
            dd($exception->getMessage());
        }
    }

    public static function getShippingCost($weight, $breakable)
    {
        switch ($weight) {
            case 'small':
                switch ($breakable) {
                    case 0:
                        return 39000;
                    case 1:
                        return 50000;
                }
                break;

            case 'medium':
                switch ($breakable) {
                    case 0:
                        return 69000;
                    case 1:
                        return 100000;
                }
                break;

            case 'large':
                switch ($breakable) {
                    case 0:
                        return 99000;
                    case 1:
                        return 150000;
                }
                break;
        }

        throw new \Exception('خطا در محاسبه هزینه پست.' . $weight. $breakable,);
    }

}
