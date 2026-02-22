<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NavbarBrand extends Model
{
    use HasFactory;

    protected $table = 'navbars_brands';

    protected $guarded = [];

    public function brand() {
        return $this->belongsTo(Brand::class,'brand_id','id');
    }
}
