<?php

namespace App\Listeners;

use App\Events\UserSanctioned;
use App\Models\Notification;

class SendUserSanctionedNotification {
    public function handle(UserSanctioned $event) {
        $sanction = $event->sanction;
        $user = $sanction->user;

        Notification::create([
            'user_id' => $user->id,
            'type' => 'sanction',
            'message' => "You have received a {$sanction->sanction_type} sanction.",
            'data' => [
                'sanction_type' => $sanction->sanction_type,
                'reason' => $sanction->reason,
                'end_date' => $sanction->end_date,
            ],
            'is_read' => false
        ]);
    }
}
