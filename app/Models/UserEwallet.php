<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEwallet extends Model
{
    use HasFactory;

    protected $table = 'users_ewallets';

    protected $guarded = [];

}
