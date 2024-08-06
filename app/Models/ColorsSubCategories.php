<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ColorsSubCategories extends Model
{
    use HasFactory;

    protected $table = 'colors_sub_categories';

    protected $hidden = ['created_at', 'updated_at'];

}
