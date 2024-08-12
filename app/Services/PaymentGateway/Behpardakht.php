<?php


namespace App\Services\PaymentGateway;


use App\Models\Order;
use App\Models\OrderLog;
use Illuminate\Http\Request;

class Behpardakht implements PaymentGateway
{

    public function __construct()
    {
    }

    public function TransactionCreate($orderId, $payer, $price, $callback)
    {
        $parameters = [
            'terminalId' => env('BEH_TERMINAL_ID'),
            'userName' => env('BEH_USERNAME'),
            'userPassword' => env('BANK_MELLAT_USER_PASSWORD', '54971171'),
            'orderId' => $orderId,
            'amount' => $price,
            'localDate' => date('Ymd'),
            'localTime' => date('Gis'),
            'additionalData' => null,
            'callBackUrl' => $callback,
            'payerId' => $payer->id
        ];

        OrderLog::create([
            'price' => $price,
            'order_id' => $orderId,
        ]);

        $client = new \nusoap_client(env('BEH_END_POINT'));

        $result = $client->call('bpPayRequest', $parameters, env('BEH_NAMESPACE'));

        if ($client->fault) {
            OrderLog::where('order_id', $orderId)->update([
                'content' => json_encode($client->faultstring)
            ]);
            return $client->faultstring;
        }

        $err = $client->getError();
        if ($err) {
            OrderLog::where('order_id', $orderId)->update([
                'content' => json_encode($err)
            ]);
            return $err;
        }

        $res = explode(',', $result);
        $ResCode = $res[0];

        if ($ResCode != "0") {
            OrderLog::where('order_id', $orderId)->update([
                'content' => json_encode($res)
            ]);
//            return 'خطایی رخ داده است:' . $ResCode;
            return $ResCode;
        }
        OrderLog::where('order_id', $orderId)->update([
            'reference' => $res[1],
        ]);
        return $res[1];
    }

    /*
     * if card number variable not pass the method won't check the card number in the response
     */
    public function TransactionCallback(Request $request, $cardNumber = null)
    {
        if ($request->ResCode != "0") {
            OrderLog::where('order_id', $request->SaleOrderId)->update([
                'content' => json_encode($request->all()),
            ]);
//            return 'خطایی رخ داده است:' . $request->ResCode;
            return $request->ResCode;   // Cancel
        }

        if (!is_null($cardNumber)) {
            $startNumbersUserCard = substr($cardNumber, 0, 6);
            $endNumbersUserCard = substr($cardNumber, -4, -1);

            $startNumbersPayedCard = substr($request->CardHolderPan, 0, 6);
            $endNumbersPayedCard = substr($request->CardHolderPan, -4, -1);

            if ($startNumbersUserCard != $startNumbersPayedCard || $endNumbersUserCard != $endNumbersPayedCard) {
                OrderLog::where('order_id', $request->SaleOrderId)->update([
                    'content' => 'invalid card number',
                ]);
                return 701;
//                return 'شماره کارت پرداختی با شماره کارت درخواست شده مطابقت ندارد.';
            }
        }

        $orderId = $request->SaleOrderId;
        $saleReferenceId = $request->SaleReferenceId;
        $refId = $request->RefId;
        $finalAmount = $request->FinalAmount;
        $cardHolderInfo = $request->CardHolderInfo ?? null;
        $cardHolderPan = $request->CardHolderPan ?? null;

        OrderLog::where('order_id', $orderId)->update([
            'reference' => $saleReferenceId
        ]);

        return (object)[
            'ref_id' => $refId,
            'order_id' => $orderId,
            'sale_reference' => $saleReferenceId,
            'price' => $finalAmount,
            'card_holder_pan' => $cardHolderPan,
            'card_holder_info' => $cardHolderInfo,
        ];
    }

    public function TransactionVerify($orderId, $transactionId, $reference, $amount)
    {
        $parameters = [
            'terminalId' => env('BEH_TERMINAL_ID'),
            'userName' => env('BEH_USERNAME'),
            'userPassword' => env('BANK_MELLAT_USER_PASSWORD', '54971171'),
            'orderId' => $orderId,
            'saleOrderId' => $orderId,
            'saleReferenceId' => $reference,
        ];

        $client = new \nusoap_client(env('BEH_END_POINT'));

        $verify = $client->call('bpVerifyRequest', $parameters, env('BEH_NAMESPACE'));

        if ($client->fault) {
            OrderLog::where('order_id', $orderId)->update([
                'content' => json_encode($client->faultstring)
            ]);
            return $client->faultstring;
        }

        $err = $client->getError();

        if ($err) {
            OrderLog::where('order_id', $orderId)->update([
                'content' => json_encode($err)
            ]);
            return $err;
        }

        // Call the SOAP method
        $settle = $client->call('bpSettleRequest', $parameters, env('BEH_NAMESPACE'));
        // Check for a fault
        if ($client->fault) {
            OrderLog::where('order_id', $orderId)->update([
                'content' => json_encode($client->faultstring)
            ]);
            return $client->faultstring;
        }
        $err = $client->getError();

        if ($err) {
            OrderLog::where('order_id', $orderId)->update([
                'content' => json_encode($err)
            ]);
            return $err;
        }

        if ($settle == '0' && $verify == '0') {
            return true;
        }

        OrderLog::where('order_id', $orderId)->update([
            'content' => json_encode(['settle' => $settle, 'verify' => $verify])
        ]);

        return 'خطایی رخ داده است.';
    }

    public function RedirectToGateway($refId)
    {
        if (empty($refId)) {
            return false;
        }


        $order = Order::where('ref_id', $refId)->with('customerCard')->with('user')->first();

        if (is_object($order) && $order->status == 'success') {
            $deepLink = DeeplinkController::getDeeplinkControllerInstance()->checkDeepLink(null, $order);
            return view('before_payed_link', ['refId' => $refId, 'orderId' => $order->id, 'saleReference' => $order->sale_reference, 'amount' => $order->amount, 'deepLink' => $deepLink]);

        } elseif (is_object($order) && $order->status != 'success') {
            return false;
        }
        return view('ban', compact('refId'));
    }
}
