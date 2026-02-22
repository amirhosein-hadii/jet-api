<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NavbarProducts extends Model
{
    use HasFactory;

    protected $table = 'navbars_products';

    protected $hidden = ['created_at', 'updated_at'];

}
