<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';
    protected $guarded = [];

    public function notificationSeen()
    {
        return $this->hasOne(NotificationSeen::class, 'notification_id', 'id');
    }

    public function storeNotification($title, $content, $user_id_to = null, $short_content = null, $for_whom = 'customer', $type = 'in_app', $links = null, $image = null, $video = null, $aparat = null, $user_id_from = null,  $seen = 0, $expire_at = null)
    {
        $notification = new self([
            'title'         => $title,
            'content'       => $content,
            'short_content' => $short_content,
            'for_whom'      => $for_whom,
            'type'          => $type,
            'links'         => $links,
            'image'         => $image,
            'video'         => $video,
            'aparat'        => $aparat,
            'user_id_from'  => $user_id_from,
            'user_id_to'    => $user_id_to,
            'seen'          => $seen,
            'expire_at'     => $expire_at,
        ]);
        $notification->save();

        return $notification;
    }

}
