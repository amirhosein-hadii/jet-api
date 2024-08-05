<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationProvince extends Model
{
    use HasFactory;

    protected $table = 'location_provinces';

    protected $hidden = ['created_at', 'updated_at'];

}
