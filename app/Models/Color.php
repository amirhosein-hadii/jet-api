<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;

    protected $table = 'colors';

    protected $hidden = ['created_at', 'updated_at'];

    public function subColors() {
        return $this->hasMany(ColorsSubCategories::class, 'color_id');
    }

}
