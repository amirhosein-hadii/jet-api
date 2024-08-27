<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $hidden = ['created_at', 'updated_at'];

    public function images() {
        return $this->hasMany(ProductImage::class,'product_id','id')->where('products_images.status', 'active');
    }

    public function brand() {
        return $this->belongsTo(Brand::class,'brand_id','id');
    }

    public function properties() {
        return $this->hasMany(ProductsPropertiesValue::class,'product_id','id');
    }

    public function importanceProperties() {
        return $this->hasMany(ProductsPropertiesValue::class,'product_id','id')->where('importance',1);
    }

    public function productVendors() {
        return $this->hasMany(VendorProduct::class,'product_id','id');
    }

    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'vendors_products', 'product_id', 'vendor_id');
    }
}
