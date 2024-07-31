<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsPropertiesValue extends Model
{
    use HasFactory;

    protected $table = 'products_properties_value';

    protected $hidden = ['created_at', 'updated_at'];


    public function title() {
        return $this->belongsTo(PropertiesTitle::class,'property_title_id','id');
    }
}
