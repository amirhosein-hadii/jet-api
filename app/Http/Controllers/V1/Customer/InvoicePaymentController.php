<?php

namespace App\Http\Controllers\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\BehpardakhtController;
use App\Models\ProductsInventoryNumChanges;
use App\Models\UserEwallet;
use App\Models\UsersBasket;
use App\Models\UsersInvoice;
use App\Models\VendorProduct;
use App\Services\Ewallet;
use Illuminate\Support\Facades\DB;


class InvoicePaymentController extends Controller
{
    private $ewallet;

    public function __construct()
    {
        $this->ewallet = new Ewallet();
    }

    public function redirectToGateway($invoiceId)
    {
        return (new BehpardakhtController())->createTransactions($invoiceId);
    }

    public function donePaymentProcess($order, $userEwalletId)
    {
        try {
            DB::beginTransaction();

            $invoice = UsersInvoice::query()->with('userInvoiceProducts.vendorProduct')->where('id', $order->invoice_id)->where('status', 'waiting')->firstOrFail();
            $invoice->status = 'success';
            $invoice->save();

            //  consume inventory_num
            InvoicePaymentController::consumeInventoryNumAfterPaid($invoice->userInvoiceProducts);

            // Payment consume
            $PaymentConsumeRes = $this->ewallet->createTransaction('payment_consume', $order->amount, $userEwalletId, $invoice->id);
            if ( !isset($PaymentConsumeRes['status']) || $PaymentConsumeRes['status'] <> 200 || !isset($PaymentConsumeRes['ewallet_transaction_id']) ) {
                throw new \Exception($res['message'] ?? 'خطایی رخ داده است.');
            }

            // Settlement with the vendor owner and agency
            $this->settlementWithVendorOwnerAndAgency($invoice->userInvoiceProducts);

            // Clear basket
            UsersBasket::query()->where('user_id', $order->user_id)->where('next_purchase', 0)->delete();

            DB::commit();

            return ['status' => 200, 'msg' => null];

        } catch (\Exception $exception) {
            DB::rollBack();

            return ['status' => 400, 'msg' => $exception->getMessage()];
        }
    }

    public function settlementWithVendorOwnerAndAgency($invoiceProducts)
    {
        foreach ($invoiceProducts as $invoiceProduct)
        {
            $vendorOwnerId = $invoiceProduct->vendorProduct->vendor->vendorUser->first()->user_id;
            $merchantFee = $invoiceProduct->vendorProduct->vendor->merchant_fee;

            $vendorShare = (100 - $merchantFee) / 100 * $invoiceProduct->paid_price ;
            $agencyShare = $merchantFee / 100 * $invoiceProduct->paid_price ;

            $vendorUserEwallet = UserEwallet::query()->where('user_id', $vendorOwnerId)->first();

            // TODO if $PaymentEarnRes return 400
            $PaymentEarnRes = $this->ewallet->createTransaction('payment_earn', $vendorShare, $vendorUserEwallet->ewallet_id, $invoiceProduct->invoice_id, $invoiceProduct->id); // Vendor
            $PaymentEarnRes = $this->ewallet->createTransaction('payment_earn', $agencyShare, null, $invoiceProduct->invoice_id, $invoiceProduct->id); // Agency
        }
    }

    public static function consumeInventoryNumAfterPaid($invoiceProducts)
    {
        foreach ($invoiceProducts as $invoiceProduct)
        {
            $changes[] = [
                'product_vendor_id'         => $invoiceProduct->vendor_product_id,
                'old_inventory_num'         => $invoiceProduct->vendorProduct->inventory_num,
                'new_inventory_num'         => $invoiceProduct->vendorProduct->inventory_num - 1,
                'users_invoices_product_id' => $invoiceProduct->id,
            ];

            VendorProduct::query()->where('id', $invoiceProduct->vendorProduct->id)->decrement('inventory_num');
        }

        ProductsInventoryNumChanges::query()->insert($changes);
    }
}
