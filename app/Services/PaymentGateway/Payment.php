<?php


namespace App\Services\PaymentGateway;


use Illuminate\Http\Request;

class Payment
{
    protected $psp;

    public function __construct(PaymentGateway $psp)
    {
        $this->psp = $psp;
    }

    public function create($orderId, $user, $price, $callback)
    {
        return $this->psp->TransactionCreate($orderId, $user, $price, $callback);
    }

    public function redirect($transactionId)
    {
        return $this->psp->RedirectToGateway($transactionId);
    }

    public function callback(Request $request, $cardNumber = null)
    {
        return $this->psp->TransactionCallback($request, $cardNumber);
    }

    public function verify($orderId, $transactionId, $reference, $amount)
    {
        return $this->psp->TransactionVerify($orderId, $transactionId, $reference, $amount);
    }
}
