<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersBasket extends Model
{
    use HasFactory;

    protected $table = 'users_basket';

    protected $guarded = [];

    public function vendorProduct() {
        return $this->belongsTo(VendorProduct::class,'vendor_product_id', 'id');
    }
}
