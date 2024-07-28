<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertiesSuperLabel extends Model
{
    use HasFactory;

    protected $table = 'properties_super_lable';

    protected $guarded = [];

    public function titles() {
        return $this->hasMany(PropertiesTitle::class,'property_super_label_id','id');
    }

}
