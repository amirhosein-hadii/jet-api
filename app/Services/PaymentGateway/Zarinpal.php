<?php


namespace App\Services\PaymentGateway;

use App\Models\Order;
use App\Models\OrderLog;
use Illuminate\Http\Request;

class Zarinpal implements PaymentGateway
{
    const MERCHANT_ID = '';
    const REQUEST_URL = 'https://api.zarinpal.com/pg/v4/payment/request.json';
    const VERIFY_URL = 'https://api.zarinpal.com/pg/v4/payment/verify.json';

    public function TransactionCreate($orderId, $payer, $amount, $callback)
    {
        try {
            $orderLog = OrderLog::initOrderLog($orderId, $amount);

            $data = array("merchant_id" => self::MERCHANT_ID,
                "amount" => $amount,
                "callback_url" => $callback,
                "description" => "خرید تست",
                "metadata" => ["email" => null, "mobile" => $payer->cellphone],
            );
            $jsonData = json_encode($data);
            $ch = curl_init(self::REQUEST_URL);
            curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData)
            ));

            $result = curl_exec($ch);
            $response = json_decode($result, true, JSON_PRETTY_PRINT);
            curl_close($ch);

            if (!empty($response['errors'])) {
                $errMsg = "(<strong> کد خطا : " . $response['errors']['code'] . "</strong>) " . $response['errors']['message'];

            } elseif (isset($response['data']) && $response['data']['code'] === 100 && $response['data']['message'] === "Success") {
                return ['status' => 200, 'msg' => $response['data']['message'], 'data' => ['authority' => $response['data']['authority']]];
            }
        } catch (\Exception $exception) {
            $errMsg = $exception->getMessage();
        }

        if ($errMsg) {
            $message = $errMsg;
        } else {
            $message = 'جوابی از طرف سرویس دهنده داده نشد';
        }

        $orderLog->update(['content' => $message]);
        return ['status' => 400, 'msg' => $message, 'data' => null];
    }

    public function RedirectToGateway($saleReference)
    {
        if (is_null($saleReference) || empty($saleReference) || $saleReference == "") {
            return false;
        }

        $order = Order::where('sale_reference', $saleReference)->with('user')->first();

        if (is_object($order) && $order->status == 'success') {
            $deepLink = '';
            return view('before_payed_link', ['refId' => $order->ref_id, 'orderId' => $order->id, 'saleReference' => $order->sale_reference, 'amount' => $order->amount, 'deepLink' => $deepLink]);

        } elseif (is_object($order) && $order->status != 'success') {
            return false;
        }

        return view('zarinpal_redirect', compact('saleReference'));
    }

    public function TransactionCallback(Request $request, $order = null)
    {
        $status = $request->Status;
        $authority = $request->Authority;

        if (isset($authority) && !empty($authority) && !is_null($authority) && $status === 'OK') {
            return ['status' => 200, 'msg' => '', 'data' => null];

        } elseif ($status === 'NOK') {
            $errMsg = " کد خطای ارسال شده از طرف بانک $status " . "";
        } else {
            $errMsg = "پاسخی از سمت بانک ارسال نشد " ;
        }

        OrderLog::where('order_id', $request->OrderId)->update([
            'content' => json_encode($request->all()),
        ]);
        return ['status' => 400, 'msg' => $errMsg, 'data' => null];
    }

    public function TransactionVerify($orderId, $authority, $status, $amount)
    {
        if (isset($authority) && $authority != "" && !is_null($authority) && $status === 'OK') {
            try {
                $jsonData = json_encode([
                    "merchant_id" => self::MERCHANT_ID,
                    "authority" => $authority,
                    "amount" => $amount
                ]);

                $ch = curl_init(self::VERIFY_URL);
                curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v4');
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($jsonData)
                ));

                $result = curl_exec($ch);
                curl_close($ch);
                $response = json_decode($result, true);

                if (!empty($response['errors'])) {
                    $errMsg = "(<strong> کد خطا : " . $response['errors']['code'] . "</strong>) " . $response['errors']['message'];
                }
            } catch (\Exception $exception) {
                $errMsg = $exception->getMessage();
            }
        } elseif ($status === 'NOK') {
            $errMsg = " کد خطای ارسال شده از طرف بانک $status " . "";
        } else {
            $errMsg = "پاسخی از سمت بانک ارسال نشد " ;
        }

        if (isset($errMsg)) {
            OrderLog::where('order_id', $orderId)->update([
                'content' => $errMsg
            ]);
            return ['status' => 400, 'msg' => $errMsg, 'data' => null];
        }

        return ['status' => 200, 'msg' => '', 'data' => ['confirmed' => $response['data']]];

//        "data": {
//        "code": 100,
//        "message": "Verified",
//        "card_hash": "1EBE3EBEBE35C7EC0F8D6EE4F2F859107A87822CA179BC9528767EA7B5489B69",
//        "card_pan": "502229******5995",
//        "ref_id": 201,
//        "fee_type": "Merchant",
//        "fee": 0
//      }

    }
}
