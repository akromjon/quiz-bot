<?php

namespace App\Notifications;


class TelegramNotificationChannel
{
    /**
     * Send the given notification.
     */
    public function send(mixed $notifiable, mixed $notification): void
    {
        $notification->toTelegram($notifiable);
    }
}
