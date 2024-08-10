<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $table = 'vendors';

    public function vendorUser() {
        return $this->hasMany(VendorsUsers::class,'vendor_id','id');
    }

}
