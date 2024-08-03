<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersBasket extends Model
{
    use HasFactory;

    protected $table = 'users_basket';

    protected $guarded = [];

    public function vendorProducts() {
        return $this->belongsTo(ProductVendor::class,'vendor_product_id', 'id');
    }
}
