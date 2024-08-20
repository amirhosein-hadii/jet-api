<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Navbar extends Model
{
    use HasFactory;

    protected $table = 'navbars';

    protected $hidden = ['created_at', 'updated_at'];

    public function navbar_products()
    {
        return $this->hasMany(NavbarProducts::class, 'navbar_id', 'id');
    }

    public function navbar_banners()
    {
        return $this->hasMany(NavbarBanners::class, 'navbar_id', 'id');
    }

    public function navbars_brands()
    {
        return $this->hasMany(NavbarBrand::class, 'navbar_id', 'id');
    }

    public function navbars_tags()
    {
        return $this->hasMany(NavbarTag::class, 'navbar_id', 'id');
    }
}
