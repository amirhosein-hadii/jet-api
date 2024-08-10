<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorProduct extends Model
{
    use HasFactory;

    protected $table = 'vendors_products';
    protected $hidden = ['created_at', 'updated_at'];

    public function product() {
        return $this->belongsTo(Product::class,'product_id','id');
    }

    public function vendor() {
        return $this->belongsTo(Vendor::class,'vendor_id','id');
    }

    public function color() {
        return $this->belongsTo(ColorsSubCategories::class,'color_id','id');
    }

    public function changeInvestorNum() {
        return $this->hasMany(ProductsInventoryNumChanges::class,'product_vendor_id','id');
    }

    public function changePrice() {
        return $this->hasMany(ProductsPriceChanges::class,'product_vendor_id','id');
    }

    public function shippings()
    {
        return $this->belongsToMany(Shipping::class, 'vendors_products_shipping', 'vendor_product_id', 'shipping_id');
    }

    public function vendors_products_shipping()
    {
        return $this->hasMany(VendorsProductsShipping::class, 'vendor_product_id', 'id');
    }
}
