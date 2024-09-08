<?php

namespace App\Notifications;

use App\Models\Transaction;

class TelegramNotificationChannel
{
    /**
     * Send the given notification.
     */
    public function send(Transaction $notifiable, TransactionApproved $notification): void
    {
        $notification->toTelegram($notifiable);
    }
}