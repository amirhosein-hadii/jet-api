<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\Behpardakht;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class BehpardakhtController extends Controller
{
    const CALL_BACK = "http://37.32.15.7:8080/api/v1/behpardakht/callback/{orderId}";

    public function createTransactions($orderId)
    {
        $order = Order::query()->find($orderId);
        if (!$order) {
            return ApiResponse::Json(400,'سفارشی یافت نشد.', [],400);
        }

        $user = User::query()->first(); // TODO Auth::user()
        $psp = new Behpardakht();
        $res = $psp->TransactionCreate($order->id, $user, $order->amount, self::CALL_BACK . $order->id);

        if (!isset($res['status']) || $res['status'] == 400 || is_null($res['refId'])) {
            return ApiResponse::Json(400,'خطایی رخ داده است.', [],400);
        }

        return $psp->RedirectToGateway($res['refId']);
    }

    public function callback($orderId, Request $request)
    {
        dd($request->all(), $orderId);
    }


}
