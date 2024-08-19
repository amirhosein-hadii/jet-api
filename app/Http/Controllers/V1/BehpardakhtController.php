<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderLog;
use App\Models\User;
use App\Models\UsersInvoice;
use App\Services\Behpardakht;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class BehpardakhtController extends Controller
{
    const CALL_BACK = "http://37.32.15.7:8080/api/v1/behpardakht/callback/";

    private $psp;

    public function __construct()
    {
        $this->psp = new Behpardakht();
    }

    public function createTransactions($orderId)
    {
        $order = Order::query()->with('user')->find($orderId);
        if (!$order) {
            return ApiResponse::Json(400,'سفارشی یافت نشد.', [],400);
        }

        $res = $this->psp->TransactionCreate($order->id, $order->user, $order->amount, self::CALL_BACK . $order->id);

        if (!isset($res['status']) || $res['status'] == 400 || is_null($res['refId'])) {
            return ApiResponse::Json(400,'خطایی رخ داده است.', [],400);
        }

        return $this->psp->RedirectToGateway($res['refId']);
    }

    public function callback($orderId, Request $request)
    {
        dd($orderId, $request->all());
        $order = Order::query()->find($orderId);
        if (!$order) {
            return ApiResponse::Json(400,'سفارشی یافت نشد.', [],400);
        }

        if ($request->ResCode != "0") {
            OrderLog::where('order_id', $request->SaleOrderId)->update([
                'content' => json_encode($request->all()),
            ]);

            return $request->ResCode;   // Cancel
        }

        $transaction = (object)[
            'ref_id' => $request->RefId,
            'order_id' => $request->SaleOrderId,
            'sale_reference' => $request->SaleReferenceId,
            'price' => $request->FinalAmount,
            'card_holder_pan' => $request->CardHolderInfo ?? null,
            'card_holder_info' => $request->CardHolderInfo ?? null,
        ];


        $result = $this->psp->TransactionVerify($orderId, $transaction->ref_id, $transaction->sale_reference);

        if ($result) {
            $order->status = 'success';
            $order->save();

            UsersInvoice::query()->where('order_id', $order->id)->update(['status' => 'success']);

            dd($request->all(), $orderId, 200);

        }

        dd($request->all(), $orderId, 400);
    }


}
