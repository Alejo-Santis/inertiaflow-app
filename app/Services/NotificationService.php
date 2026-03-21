<?php

namespace App\Services;

use App\Models\UserNotification;

class NotificationService
{
    public static function send(int $userId, string $type, string $title, ?string $message = null, ?string $url = null): void
    {
        UserNotification::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'url'     => $url,
        ]);
    }

    public static function sendToMany(array $userIds, string $type, string $title, ?string $message = null, ?string $url = null): void
    {
        $now = now();
        $rows = array_map(fn($id) => [
            'user_id'    => $id,
            'type'       => $type,
            'title'      => $title,
            'message'    => $message,
            'url'        => $url,
            'created_at' => $now,
            'updated_at' => $now,
        ], $userIds);

        UserNotification::insert($rows);
    }
}
