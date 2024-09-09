<?php

namespace App\Http\Controllers\V1\Customer;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\BehpardakhtController;
use App\Models\Order;
use App\Models\ProductsInventoryNumChanges;
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
    const SHIPPING_DATE_DILAY = 3;

    const SHIPPING_DATE_DILAY_FOR_NOT_IN_SAME_CITY = 2;

    protected $addresses;
    protected $vendor_products_id;

    public function show($id)
    {
        $userInvoiceProduct = UsersInvoicesProduct::query()
            ->join('users_addresses', 'users_invoices_products.user_address_id','=', 'users_addresses.id')
            ->join('vendors_products', 'vendors_products.id', 'users_invoices_products.vendor_product_id')
            ->join('products', 'products.id','=', 'vendors_products.product_id')
            ->join('vendors', 'vendors.id','=', 'vendors_products.vendor_id')
            ->join('colors_sub_categories', 'vendors_products.sub_color_id','=', 'colors_sub_categories.id')
            ->where('users_invoices_products.invoice_id', $id)
            ->where('users_invoices_products.user_id', Auth::id())
            ->select(
                'users_invoices_products.id', 'users_invoices_products.deliver_date_from', 'users_invoices_products.deliver_date_to',
                'users_invoices_products.origin_price', 'users_invoices_products.paid_price', 'users_invoices_products.delivery_price',
                'colors_sub_categories.name as color_name', 'colors_sub_categories.code as color_code',
                'vendors.name as vendor_name', 'vendors.tel as vendor_tel',
                'products.title as product_title',
                'users_addresses.address'
            )
            ->get();

        $userInvoiceProduct->map(function ($item) {
            $item->deliver_date_from = convertReelToDashedJalalian($item->deliver_date_from);
            $item->deliver_date_to = convertReelToDashedJalalian($item->deliver_date_to);
        });

        $totalDeliveriesAmount = $userInvoiceProduct->sum('delivery_price');
        $totalProductAmount = $userInvoiceProduct->sum('origin_price') - $totalDeliveriesAmount;
        $totalAmount = $userInvoiceProduct->sum('paid_price');

        $data = [
            'total_products_amount'   => $totalProductAmount,
            'total_deliveries_amount' => $totalDeliveriesAmount,
            'total_amount'            => $totalAmount,
            'invoice_product'         => $userInvoiceProduct
        ];

        return ApiResponse::Json(200, '', $data, 200);
    }

    public function invoiceProductList()
    {
        $products = UsersInvoice::query()
            ->join('users_invoices_products', 'users_invoices_products.invoice_id','=','users_invoices.id')
            ->join('users_addresses', 'users_invoices_products.user_address_id','=', 'users_addresses.id')
            ->join('vendors_products', 'vendors_products.id', 'users_invoices_products.vendor_product_id')
            ->join('products', 'products.id','=', 'vendors_products.product_id')
            ->join('vendors', 'vendors.id','=', 'vendors_products.vendor_id')
            ->join('colors_sub_categories', 'vendors_products.sub_color_id','=', 'colors_sub_categories.id')
            ->where('users_invoices.user_id', Auth::id())
            ->select(
                'users_invoices_products.id as invoices_products_id', 'users_invoices_products.deliver_date_from', 'users_invoices_products.deliver_date_to',
                'users_invoices_products.origin_price', 'users_invoices_products.paid_price', 'users_invoices_products.delivery_price',
                'colors_sub_categories.name as color_name', 'colors_sub_categories.code as color_code',
                'vendors.name as vendor_name', 'vendors.tel as vendor_tel',
                'products.title as product_title', 'products.avatar_link_l', 'products.id as product_id',
                'users_addresses.address',
                'users_invoices.id as invoice_id', 'users_invoices.status', 'users_invoices.tracking_code', 'users_invoices.created_at'
            )
            ->get();

        $products->map(function ($item) {
            $item->deliver_date_from = convertReelToDashedJalalian($item->deliver_date_from);
            $item->deliver_date_to   = convertReelToDashedJalalian($item->deliver_date_to);
            $item->created           = convertToDashedJalalian($item->created_at);
        });

        return ApiResponse::Json(200, '', $products, 200);
    }

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

    public function validation($request)
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
            $addresses = UserAddress::query()->where('user_id', $userId)->where('status', 'active')->whereIn('id', $selectedAddresses)->select('id', 'city_id')->get();
            if (!haveSameValues($selectedAddresses, $addresses->pluck('id')->toArray())) {
                return ['status' => 400, 'msg' => 'آدرس وارد شده اشتباه است.'];
            }
            $this->addresses = $addresses;

            // Check Basket
            $vendor_products_id = $items->pluck('vendor_product_id')->toArray();
            $baskets = UsersBasket::query()->where('user_id', $userId)->pluck('vendor_product_id')->toArray();
            if (!haveSameValues($vendor_products_id, $baskets) || count($baskets) != count($vendor_products_id)) {
                return ['status' => 400, 'msg' => 'عدم مطابقت با سبد خرید.'];
            }
            $this->vendor_products_id = $vendor_products_id;

            // Check shipping
            $vpsi = $items->pluck('vendor_product_id', 'vendors_products_shipping_id')->toArray();
            $vendorProductsShipping = VendorsProductsShipping::query()->whereIn('vendor_product_id', $vendor_products_id)->pluck('vendor_product_id', 'id')->toArray();
            foreach ($vpsi as $key => $value) {
                if (!array_key_exists($key, $vendorProductsShipping) || $vendorProductsShipping[$key] !== $value) {
                    return ['status' => 400, 'msg' => 'آدرس انتخاب شده صحیح نمی باشد.'];
                }
            }

            return ['status' => 200, 'msg' => ''];

        } catch (\Exception $exception) {
            return ['status' => 400, 'msg' => $exception->getMessage()];
        }
    }

    public function bill(Request $request)
    {
        try {
            $validationRes = $this->validation($request);
            if ($validationRes['status'] <> 200) {
                return ApiResponse::Json($validationRes['status'], $validationRes['msg'] ?? 'خطایی رخ داده است', [], $validationRes['status']);
            }

            $res = $this->calculationDeliveryDateAndPrice($request);
            if ($res['status'] <> 200) {
                return ApiResponse::Json($res['status'], $res['msg'] ?? 'خطایی رخ داده است', [], $res['status']);
            }

            $data = [
                'total_products_amount'   => $res['total_products_amount'],
                'total_deliveries_amount' => $res['total_deliveries_amount'],
                'total_amount'            => $res['total_products_amount'] + $res['total_deliveries_amount']
            ];

            return ApiResponse::Json(200, '', $data, $res['status']);

        } catch (\Exception $e) {
            return ApiResponse::Json(400, $e->getMessage(), [], 400);
        }
    }

    public function calculationDeliveryDateAndPrice($request)
    {
        try {
            $items = collect($request->all());

            $vendor_products = VendorProduct::query()->with('vendor')->whereIn('id', $this->vendor_products_id)->get();

            $totalProductsAmount = 0;
            $vendorsDeliveryPrice = [];
            $vendorsDeliveryDateFrom = [];
            $vendorsDeliveryDateTo = [];
            foreach ($items as $item)
            {
                $vendor_product = $vendor_products->where('id', $item['vendor_product_id'])->first();

                $requestCityId = $this->addresses->where('id', $item['user_address_id'])->value('city_id');
                $at_same_city =  $vendor_product->vendor->city_id == $requestCityId;

                // Calculate delivery time
                $delivery_dilay_for_not_same_city = $at_same_city ? 0 : self::SHIPPING_DATE_DILAY_FOR_NOT_IN_SAME_CITY;
                $deliver_date_from = self::SHIPPING_DATE_FROM + $delivery_dilay_for_not_same_city;
                $deliver_date_to = $deliver_date_from + self::SHIPPING_DATE_DILAY;

                $vendorsDeliveryDateFrom[$vendor_product->vendor->id] = $deliver_date_from;
                $vendorsDeliveryDateTo[$vendor_product->vendor->id] = $deliver_date_to;


                // Calculate shipping cost
                if ($vendor_product->free_delivery == 'YES') {
                    $delivery_price = 0;
                } else {
                    $shippingCost = self::getShippingCost($vendor_product->product->weight_size_level, $vendor_product->product->breakable);
                    $delivery_price = $at_same_city ? $shippingCost : $shippingCost + 50000;
                }

                if (array_key_exists($vendor_product->vendor->id, $vendorsDeliveryPrice)) {
                    $vendorsDeliveryPrice[$vendor_product->vendor->id] = max($vendorsDeliveryPrice[$vendor_product->vendor->id], $delivery_price);
                } else {
                    $vendorsDeliveryPrice[$vendor_product->vendor->id] = $delivery_price;
                }

                $totalProductsAmount += $vendor_product->price;
            }

            $totalDeliveriesPrice = 0;
            foreach ($vendorsDeliveryPrice as $deliverPrice)
            {
                $totalDeliveriesPrice += $deliverPrice;
            }

            return [
                'status'                  => 200,
                'vendors_delivery_price'  => $vendorsDeliveryPrice, // ['vendor_id' => delivery_price]
                'deliver_date_from'       => $vendorsDeliveryDateFrom,
                'deliver_date_to'         => $vendorsDeliveryDateTo,
                'total_products_amount'   => $totalProductsAmount,
                'total_deliveries_amount' => $totalDeliveriesPrice,
                'vendor_products'         => $vendor_products
            ];

        } catch (\Exception $exception) {
            return ['status' => 400, 'msg' => $exception->getMessage()];
        }
    }

    public function createInvoice(Request $request)
    {
        try {
            $validationRes = $this->validation($request);
            if ($validationRes['status'] <> 200) {
                return ApiResponse::Json($validationRes['status'], $validationRes['msg'] ?? 'خطایی رخ داده است', [],$validationRes['status']);
            }

            $res = $this->calculationDeliveryDateAndPrice($request);
            if ($res['status'] <> 200) {
                return ApiResponse::Json($res['status'], $res['msg'] ?? 'خطایی رخ داده است', [], $res['status']);
            }

            $vendor_products         = $res['vendor_products'];
            $totalAmount             = $res['total_products_amount'] + $res['total_deliveries_amount'];
            $vendorsDeliveryDateFrom = $res['deliver_date_from'];
            $vendorsDeliveryDateTo   = $res['deliver_date_to'];
            $vendorsDeliveryPrice    = $res['vendors_delivery_price'];


            $items = collect($request->all());
            $userId = Auth::id();

            DB::beginTransaction();

            $userInvoice = UsersInvoice::query()->create([
                'user_id'       => $userId,
                'status'        => 'waiting',
                'tracking_code' => rand(1111111111, 9999999999)
            ]);


            $insert = [];
            foreach ($items as $item)
            {
                $vendor_product = $vendor_products->where('id', $item['vendor_product_id'])->first();

                $delivery_price = 0;
                if (array_key_exists($vendor_product->vendor->id, $vendorsDeliveryPrice)) {
                    $delivery_price =+ $vendorsDeliveryPrice[$vendor_product->vendor->id];
                    unset($vendorsDeliveryPrice[$vendor_product->vendor->id]);
                }

                $origin_price = $vendor_product->price + $delivery_price;
                $paid_price = $origin_price; // TODO after discount

                $insert[] = [
                    'invoice_id'                   => $userInvoice->id,
                    'user_id'                      => $userId,
                    'vendor_product_id'            => $vendor_product->id,
                    'origin_price'                 => $origin_price,
                    'paid_price'                   => $paid_price,// TODO after discount
                    'vendors_products_shipping_id' => $item['vendors_products_shipping_id'],
                    'deliver_date_from'            => jalalianAddDays($vendorsDeliveryDateFrom[$vendor_product->vendor->id]),
                    'deliver_date_to'              => jalalianAddDays($vendorsDeliveryDateTo[$vendor_product->vendor->id]),
                    'delivery_price'               => $delivery_price,
                    'delivered_by_user_id'         => $userId,
                    'user_address_id'              => $item['user_address_id'],
                ];

            }

            UsersInvoicesProduct::query()->insert($insert);

            $order = Order::query()->create([
                'gateway_provider' => 'Mellat',
                'user_id'          => $userId,
                'invoice_id'       => $userInvoice->id,
                'type'             => 'purchase_payment',
                'amount'           => $totalAmount,
                'status'           => 'waiting',
            ]);

            DB::commit();

            return ApiResponse::Json(200, 'عملیات با موقیت انجام شد.', ['invoice_id' => $userInvoice->id], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::Json(400, $e->getMessage(), [], 400);
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

    public function redirectToGateway($invoiceId)
    {
        return (new BehpardakhtController())->createTransactions($invoiceId);
    }

    public static function consumeInventoryNumAfterPaid($invoiceProducts)
    {
        foreach ($invoiceProducts as $invoiceProduct)
        {
            $changes[] = [
                'product_vendor_id'         => $invoiceProduct->vendor_product_id,
                'old_inventory_num'         => $invoiceProduct->vendorProduct->inventory_num,
                'new_inventory_num'         => $invoiceProduct->vendorProduct->inventory_num - 1,
                'users_invoices_product_id' => $invoiceProduct->id,
            ];

            VendorProduct::query()->where('id', $invoiceProduct->vendorProduct->id)->decrement('inventory_num');
        }

        ProductsInventoryNumChanges::query()->insert($changes);
    }
}
