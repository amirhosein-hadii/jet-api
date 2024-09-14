<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSeen extends Model
{
    use HasFactory;

    protected $table = 'notifications_seen';
    protected $guarded = [];

}
