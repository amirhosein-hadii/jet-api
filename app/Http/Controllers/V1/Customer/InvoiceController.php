<?php

namespace App\Http\Controllers\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\UsersBasket;
use App\Models\UsersInvoice;
use App\Models\UsersInvoicesProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class InvoiceController extends Controller
{

    public function createInvoice()
    {
        try {
            $userId = Auth::id();

            $baskets = UsersBasket::query()
                ->with(['vendorProduct'])
                ->where('user_id', $userId)
                ->get();

            if (empty($baskets)) {
                return response()->json(['error' => 'سبد شما خالی می باشد.'], 400);
            }

            DB::beginTransaction();

            $userInvoice = UsersInvoice::query()->create([
                'user_id' => $userId,
                'tracking_code' => rand(1111111111, 9999999999),
            ]);

            foreach ($baskets as $item)
            {
//                UsersInvoicesProduct::create([
//                    'invoice_id'        => $userInvoice->id,
//                    'user_id'           => $userId,
//                    'vendor_product_id' => $item->vendor_product_id,
//                    'origin_price'      => $item->vendorProduct->price,
//                    'origin_price'      => $item->vendorProduct->price,
//                ]);
            }

            DB::commit();

        } catch (\Exception $exception) {

            DB::rollBack();

        }
    }
}
