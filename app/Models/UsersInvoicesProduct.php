<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersInvoicesProduct extends Model
{
    use HasFactory;

    protected $table = 'users_invoices_products';

    protected $guarded = [];

    public function invoice()
    {
        return $this->belongsTo(UsersInvoice::class, 'invoice_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function vendorProduct()
    {
        return $this->belongsTo(VendorProduct::class, 'vendor_product_id', 'id');
    }

}
