<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Services\Ewallet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class CreateUserAndEwalletInToEwallet
{

    public function handle(UserRegistered $event): void
    {
        $user = $event->user;

        $service = new Ewallet();
        $res = $service->createUser($user->cellphone);

        if (isset($res['uid'])) {
            $user->update(['ewallet_user_id' => $res['uid']]);
        }
    }
}
