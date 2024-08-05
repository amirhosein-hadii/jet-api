<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationRegion extends Model
{
    use HasFactory;

    protected $table = 'location_regions';

    protected $hidden = ['created_at', 'updated_at'];

}
