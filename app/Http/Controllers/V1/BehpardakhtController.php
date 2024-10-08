<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Customer\InvoiceController;
use App\Http\Controllers\V1\Customer\InvoicePaymentController;
use App\Models\Order;
use App\Models\OrderLog;
use App\Models\UserEwallet;
use App\Models\UsersBasket;
use App\Models\UsersInvoice;
use App\Services\Behpardakht;
use App\Services\Ewallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class BehpardakhtController extends Controller
{
    const CALL_BACK = "http://37.32.15.7:8080/api/v1/behpardakht/callback/";

    const DEEP_LINK = 'http://jetmall.ir/';
    private $psp;

    private $ewallet;

    public function __construct()
    {
        $this->psp = new Behpardakht();
        $this->ewallet = new Ewallet();
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
            return view('gateway.callback-unsuccess', ['message' => 'خطایی رخ داده است', 'refId' => $transaction->ref_id, 'orderId' => $transaction->order_id, 'saleReference' => $transaction->sale_reference, 'amount' => $transaction->price, 'deepLink' => self::DEEP_LINK]);
        }


        if (!isset($transaction->ref_id) && !isset($transaction->order_id) && !isset($transaction->price))
        {
            if ($transaction == 701)
            {
                return $this->rejectOrder($order, 'unsuccess', 'gateway.callback-unsuccess');

            } elseif ($transaction == 17) {
                return $this->rejectOrder($order, 'unsuccess', 'gateway.callback-cancel');

            } else {
                return $this->rejectOrder($order, 'unsuccess', 'gateway.callback-unsuccess', $transaction->ref_id, $transaction->order_id, $transaction->sale_reference, $transaction->price);
            }
        }

        if ($order->amount <> riyalToToman($transaction->price)) {
            return $this->rejectOrder($order, 'unsuccess', 'gateway.callback-unsuccess', $transaction->ref_id, $transaction->order_id, $transaction->sale_reference, $transaction->price);
        }

        $userEwallet = UserEwallet::query()->where('user_id', $order->user_id)->first();
        if (!$userEwallet) {
            return $this->rejectOrder($order, 'unsuccess', 'gateway.callback-unsuccess', $transaction->ref_id, $transaction->order_id, $transaction->sale_reference, $transaction->price);
        }
        $userEwalletId = $userEwallet->ewallet_id;


        // CashIn
        $cashInRes = $this->cashIn($order, $transaction, $userEwalletId);
        if (!isset($cashInRes['status']) || $cashInRes['status'] <> 200) {
            throw new \Exception($cashInRes['message'] ?? 'خطایی رخ داده است.');
        }

        // Done payment process
        $invoicePayment = new InvoicePaymentController();
        $donePaymentProcessRes = $invoicePayment->donePaymentProcess($order, $userEwalletId);
        if (!isset($donePaymentProcessRes['status']) || $donePaymentProcessRes['status'] <> 200) {
            return $this->rejectOrder($order, 'unsuccess', 'gateway.callback-unsuccess', $transaction->ref_id, $transaction->order_id, $transaction->sale_reference, $transaction->price);
        }

        return view('gateway.callback-success', ['message' => 'با موفقیت انجام شد', 'refId' => $transaction->ref_id, 'orderId' => $transaction->order_id, 'saleReference' => $transaction->sale_reference, 'amount' => riyalToToman($transaction->price), 'deepLink' => self::DEEP_LINK . "/Settlement/" . $order->invoice_id]);
    }

    public function rejectOrder($order, $status, $view, $refId = null, $orderId = null, $saleReference= null, $amount = null)
    {
        $order->status = $status;
        $order->save();
        return view($view, ['message' => 'خطایی رخ داده است', 'refId' => $refId, 'orderId' => $orderId, 'saleReference' => $saleReference, 'amount' => riyalToToman($amount), 'deepLink' => self::DEEP_LINK, 'type' => 'customer']);
    }

    public function cashIn($order, $transaction, $userEwalletId)
    {
        try {
            $CashInRes = $this->ewallet->createTransaction( 'cache-in', $order->amount, $userEwalletId);

            if ( !isset($CashInRes['status']) || $CashInRes['status'] <> 200 || !isset($CashInRes['ewallet_transaction_id']) ) {
                throw new \Exception($res['message'] ?? 'خطایی رخ داده است.');
            }


            $result = $this->psp->TransactionVerify($transaction->order_id, $transaction->ref_id, $transaction->sale_reference);
            if ($result['status'] <> 200) {
                throw new \Exception($res['message'] ?? 'خطایی رخ داده است.');
            }

            $order->status           = 'success';
            $order->ref_id           = $transaction->ref_id;
            $order->sale_reference   = $transaction->sale_reference;
            $order->card_holder_info = $transaction->card_holder_pan;
            $order->card_holder_pan  = $transaction->card_holder_info;
            $order->save();

            return ['status' => 200, 'msg' => null];

        } catch (\Exception $exception) {
            $order->status           = 'unsuccess';
            $order->description      = 'ewallet_cashed_in_successfully_but_not_banking_verified';
            $order->ref_id           = $transaction->ref_id;
            $order->sale_reference   = $transaction->sale_reference;
            $order->card_holder_info = $transaction->card_holder_pan;
            $order->card_holder_pan  = $transaction->card_holder_info;
            $order->save();

            return ['status' => 400, 'msg' => $exception->getMessage()];
        }
    }
}
