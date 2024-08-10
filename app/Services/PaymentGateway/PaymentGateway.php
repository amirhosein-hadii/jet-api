<?php


namespace App\Services\PaymentGateway;

use Illuminate\Http\Request;

interface PaymentGateway
{
    public function TransactionCreate($orderId, $payerId, $price, $callback);

    public function TransactionCallback(Request $request, $cardId = null);

    public function TransactionVerify($orderId, $transactionId, $reference, $amount);

    public function RedirectToGateway($transactionId);
}
