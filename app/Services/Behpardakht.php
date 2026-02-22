<?php


namespace App\Services;


use App\Models\Order;
use App\Models\OrderLog;

use Illuminate\Http\Request;

class Behpardakht
{

    public function __construct()
    {
    }

    public function TransactionCreate($orderId, $payer, $price, $callback)
    {
        $parameters = [
            'terminalId'     => env('BEH_TERMINAL_ID'),
            'userName'       => env('BEH_USERNAME'),
            'userPassword'   => env('BANK_MELLAT_USER_PASSWORD', '54971171'),
            'orderId'        => $orderId,
            'amount'         => tomanToriyal($price),
            'localDate'      => date('Ymd'),
            'localTime'      => date('Gis'),
            'additionalData' => null,
            'callBackUrl'    => $callback,
            'payerId'        => $payer->id
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
            return ['status' => 400, 'msg' => $client->faultstring];
        }

        $err = $client->getError();
        if ($err) {
            OrderLog::where('order_id', $orderId)->update([
                'content' => json_encode($err)
            ]);
            return ['status' => 400, 'msg' => $err, 'refId' => null];
        }

        $res = explode(',', $result);
        $ResCode = $res[0];

        if ($ResCode != "0") {
            OrderLog::where('order_id', $orderId)->update([
                'content' => json_encode($res)
            ]);
//            return 'خطایی رخ داده است:' . $ResCode;
            return ['status' => 400, 'msg' => $ResCode, 'refId' => null];
        }

        OrderLog::where('order_id', $orderId)->update([
            'reference' => $res[1],
        ]);

        return ['status' => 200, 'msg' => 'success', 'refId' => $res[1]];
    }

    /*
     * if card number variable not pass the method won't check the card number in the response
     */
    public function TransactionCallback(Request $request, $cardNumber = null)
    {
        if ($request->ResCode != "0") {
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


        return (object)[
            'ref_id' => $refId,
            'order_id' => $orderId,
            'sale_reference' => $saleReferenceId,
            'price' => $finalAmount,
            'card_holder_pan' => $cardHolderPan,
            'card_holder_info' => $cardHolderInfo,
        ];
    }

    public function TransactionVerify($orderId, $transactionId, $reference)
    {
        try {
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
                throw new \Exception(json_encode($client->faultstring));
            }

            $err = $client->getError();

            if ($err) {
                OrderLog::where('order_id', $orderId)->update([
                    'content' => json_encode($err)
                ]);
                throw new \Exception(json_encode($err));
            }

            // Call the SOAP method
            $settle = $client->call('bpSettleRequest', $parameters, env('BEH_NAMESPACE'));
            // Check for a fault
            if ($client->fault) {
                OrderLog::where('order_id', $orderId)->update([
                    'content' => json_encode($client->faultstring)
                ]);
                throw new \Exception(json_encode($client->faultstring));
            }

            $err = $client->getError();

            if ($err) {
                OrderLog::where('order_id', $orderId)->update([
                    'content' => json_encode($err)
                ]);
                throw new \Exception(json_encode($err));
            }

            if ($settle == '0' && $verify == '0') {
                return ['status' => 200, 'msg' => ''];
            }

            OrderLog::where('order_id', $orderId)->update([
                'content' => json_encode(['settle' => $settle, 'verify' => $verify])
            ]);
            throw new \Exception('خطایی رخ داده است.');

        } catch (\Exception $e) {
            return ['status' => 400, 'msg' => $e->getMessage()];
        }
    }
}
