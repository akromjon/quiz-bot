<?php

namespace App\Notifications;

use App\Models\Enums\TelegramUserStatusEnum;
use App\Models\Enums\TelegramUserTariffEnum;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Telegram\Menu\Menu;

class TransactionRejectedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Transaction $transaction)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [TelegramNotificationChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */

    public function toTelegram(Transaction $transaction): void
    {
        // Update user's table: tariff = 'paid', tariff, last_payment_date = now(), next_payment_date = now() + 1 month

        $user = $transaction->telegramUser;

        // Send a message to the user that the receipt has been approved
        Telegram::sendMessage([...Menu::profile($user->user_id)]);
        Telegram::sendMessage(Menu::receiptRejected($user));
        Telegram::sendMessage([...Menu::base()]);

    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
