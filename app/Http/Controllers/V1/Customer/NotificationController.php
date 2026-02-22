<?php

namespace App\Http\Controllers\V1\Customer;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class NotificationController extends Controller
{
    public function getNotifications(Request $request)
    {
        $user = Auth::user();

        $globalNotif = Notification::query()
            ->where('for_whom', 'customer')
            ->where('type', 'in_app')
            ->where('created_at', '>=', $user->created_at);
//            ->where('expire_at', '>=', Carbon::now());

        $notifs = Notification::query()
            ->where('user_id_to', $user->id)
            ->where('type', 'in_app');
//            ->where('expire_at', '>=', Carbon::now());

        if ($request->filled('unseen')) {
            $globalNotif->doesntHave('notificationSeen', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

            $notifs->where('seen', 0);
        }

        $allNotifs = $notifs->union($globalNotif)->orderBy('id', 'desc')->paginate(10);

        return ApiResponse::Json(200, '', $allNotifs, 200);
    }
}
