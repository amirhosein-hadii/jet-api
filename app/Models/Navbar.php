<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Navbar extends Model
{
    use HasFactory;

    protected $table = 'navbars';

    public function navbar_products()
    {
        return $this->hasMany(NavbarProducts::class, 'navbar_id', 'id');
    }

    public function navbar_banners()
    {
        return $this->hasMany(NavbarBanners::class, 'navbar_id', 'id');
    }
}
