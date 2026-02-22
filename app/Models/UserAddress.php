<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory;

    protected $table = 'users_addresses';

    protected $hidden = ['created_at', 'updated_at'];


    public function city() {
        return $this->belongsTo(LocationCities::class,'city_id','id');
    }

}
