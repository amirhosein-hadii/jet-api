<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NavbarTag extends Model
{
    use HasFactory;

    protected $table = 'navbars_tags';

    protected $guarded = [];

    public function tag() {
        return $this->belongsTo(Tag::class,'tag_id','id');
    }
}
