<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PaymentGateway\Behpardakht;
use App\Services\PaymentGateway\Payment;


class BehpardakhtController extends Controller
{
    public function createTransactions()
    {
//        $order = Order::query()->first();
        $user = User::query()->first();
        $payment = new Payment(new Behpardakht());
        $res = $payment->create(1, $user,100000,"http://37.32.15.7:8080/api/v1/order/callback");
        dd($res);
    }
}
