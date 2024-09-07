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
        return $this->hasMany(ProductsPropertiesValue::class,'product_id','id')
            ->join('properties_title', 'properties_title.id', 'products_properties_value.property_title_id')
            ->select('products_properties_value.name as value_name', 'products_properties_value.product_id',
                'properties_title.name as title_name', 'properties_title.priority', 'properties_title.id as title_id'
            )
            ->orderBy('priority', 'desc')
            ->where('importance',1);
    }

    public function productVendors() {
        return $this->hasMany(VendorProduct::class,'product_id','id');
    }

    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'vendors_products', 'product_id', 'vendor_id');
    }
}
