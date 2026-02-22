<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertiesTitle extends Model
{
    use HasFactory;

    protected $table = 'properties_title';
    protected $hidden = ['created_at', 'updated_at'];

    public function ProductPropertiesValues() {
        return $this->hasMany(ProductsPropertiesValue::class,'property_title_id','id');
    }

}
