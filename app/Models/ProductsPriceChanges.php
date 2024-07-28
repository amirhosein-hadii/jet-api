<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsPriceChanges extends Model
{
    use HasFactory;

    protected $table = 'products_price_changes';

    protected $guarded = [];

}
