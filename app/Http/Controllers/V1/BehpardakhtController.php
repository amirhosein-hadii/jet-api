<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderLog;
use App\Models\UserEwallet;
use App\Models\UsersInvoice;
use App\Services\Behpardakht;
use App\Services\Ewallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class BehpardakhtController extends Controller
{
    const CALL_BACK = "http://37.32.15.7:8080/api/v1/behpardakht/callback/";

    const DEEP_LINK = 'http://37.32.15.7:3000/';
    private $psp;

    public function __construct()
    {
        $this->psp = new Behpardakht();
    }

    public function createTransactions($invoiceId)
    {
        try {
            $invoice = UsersInvoice::query()
                ->where('user_id', Auth::id())
                ->find($invoiceId);
            if (!$invoice) {
                throw new \Exception('فاکتوری یافت نشد.');
            }

            $order = Order::query()->with('user')
                ->where('user_id', Auth::id())
                ->where('invoice_id', $invoiceId)
                ->first();
            if (!$order) {
                throw new \Exception('سفارشی یافت نشد.');

            } elseif ($order->status == 'success') {
                throw new \Exception('سفارش قبلا پرداخت شده است.');
            }

            $res = $this->psp->TransactionCreate($order->id, $order->user, $order->amount, self::CALL_BACK . $order->id);

            if (!isset($res['status']) || $res['status'] == 400 || is_null($res['refId'])) {
                throw new \Exception('خطا در اتصال به درگاه.');
            }

            return view('gateway.redirect_to_bank', ['refId' => $res['refId']]);

        } catch (\Exception $e) {
            return view('gateway.error_in_redirect_to_bank', ['message' => $e->getMessage(), 'deepLink' => self::DEEP_LINK]);
        }
    }

    public function callback($orderId, Request $request)
    {
        OrderLog::query()->insert(['order_id' => $request->SaleOrderId, 'content' => json_encode($request->all()) ]);

        $order = Order::with('user')->where('id', $orderId)->where('status', 'waiting')->first();

        $transaction = $this->psp->TransactionCallback($request);

        if (!$order) {
            return view('gateway.callback-unsuccess', ['message' => 'خطایی رخ داده است', 'refId' => $transaction->ref_id, 'orderId' => $transaction->order_id, 'saleReference' => $transaction->sale_reference, 'amount' => riyalToToman($transaction->price), 'deepLink' => self::DEEP_LINK]);
        }


        if (!isset($transaction->ref_id) && !isset($transaction->order_id) && !isset($transaction->price))
        {
            if ($transaction == 701)
            {
                return $this->rejectOrder($order, 'unsuccess', 'gateway.callback-unsuccess');

            } elseif ($transaction == 17) {
                return $this->rejectOrder($order, 'unsuccess', 'gateway.callback-cancel');

            } else {
                return $this->rejectOrder($order, 'unsuccess', 'gateway.callback-unsuccess', $transaction->ref_id, $transaction->order_id, $transaction->sale_reference, riyalToToman($transaction->price));
            }
        }

        if ($order->amount <> riyalToToman($transaction->price)) {
            return $this->rejectOrder($order, 'unsuccess', 'gateway.callback-unsuccess', $transaction->ref_id, $transaction->order_id, $transaction->sale_reference, riyalToToman($transaction->price));
        }

        try {
            $userEwallet = UserEwallet::query()->where('user_id', $order->user_id)->first();
            if (!$userEwallet) {
                return $this->rejectOrder($order, 'unsuccess', 'gateway.callback-unsuccess', $transaction->ref_id, $transaction->order_id, $transaction->sale_reference, riyalToToman($transaction->price));
            }

            $ewallet = new Ewallet();
            $CashInRes = $ewallet->createTransaction($userEwallet->id, 'cache-in', $order->amount);

            if ( !isset($CashInRes['status']) || $CashInRes['status'] <> 200 || !isset($CashInRes['data']['ewallet_transaction_id']) ) {
                throw new \Exception($res['message'] ?? 'خطایی رخ داده است.');
            }

            $result = $this->psp->TransactionVerify($transaction->order_id, $transaction->ref_id, $transaction->sale_reference);
            if ($result['status'] <> 200) {
                throw new \Exception($res['message'] ?? 'خطایی رخ داده است.');
            }

            DB::beginTransaction();

            $order->status           = 'success';
            $order->ref_id           = $transaction->ref_id;
            $order->sale_reference   = $transaction->sale_reference;
            $order->card_holder_info = $transaction->card_holder_pan;
            $order->card_holder_pan  = $transaction->card_holder_info;
            $order->save();

            $invoice = UsersInvoice::query()->where('id', $order->invoice_id)->where('status', 'waiting')->firstOrFail();
            $invoice->status = 'success';
            $invoice->save();
            // TODO consume inventory_num

            $PaymentConsumeRes = $ewallet->createTransaction($userEwallet->id, 'payment_consume', $order->amount);
            if ( !isset($PaymentConsumeRes['status']) || $PaymentConsumeRes['status'] <> 200 || !isset($PaymentConsumeRes['data']['ewallet_transaction_id']) ) {
                throw new \Exception($res['message'] ?? 'خطایی رخ داده است.');
            }

            DB::commit();


        } catch (\Exception $exception) {
            DB::rollBack();
            $order->status           = 'unsuccess';
            $order->description      = 'ewallet_cashed_in_successfully_but_not_banking_verified';
            $order->save();
            return $this->rejectOrder($order, 'unsuccess', 'gateway.callback-unsuccess', $transaction->ref_id, $transaction->order_id, $transaction->sale_reference, riyalToToman($transaction->price));
        }

        return view('gateway.callback-success', ['message' => 'با موفقیت انجام شد', 'refId' => $transaction->ref_id, 'orderId' => $transaction->order_id, 'saleReference' => $transaction->sale_reference, 'amount' => $transaction->price / 10, 'deepLink' => self::DEEP_LINK . "/Settlement/" . $order->invoice_id]);
    }

    public function rejectOrder($order, $status, $view, $refId = null, $orderId = null, $saleReference= null, $amount = null)
    {
        $order->status = $status;
        $order->save();
        return view($view, ['message' => 'خطایی رخ داده است', 'refId' => $refId, 'orderId' => $orderId, 'saleReference' => $saleReference, 'amount' => riyalToToman($amount), 'deepLink' => self::DEEP_LINK, 'type' => 'customer']);
    }
}
