<?php

namespace App\Notifications;

use App\Models\Enums\TelegramUserStatusEnum;
use App\Models\Enums\TelegramUserTariffEnum;
use App\Models\TelegramUser;
use App\Models\Transaction;
use App\Telegram\Menu\Menu;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Telegram\Bot\Laravel\Facades\Telegram;

class TransactionApprovedNotification extends Notification
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

        if ($user->tariff === TelegramUserTariffEnum::PAID) {

            $user->update([
                'tariff' => TelegramUserTariffEnum::PAID,
                'status' => TelegramUserStatusEnum::ACTIVE,
                'balance' => $user->balance + $transaction->amount,
                'last_payment_date' => now(),
            ]);
        }

        if ($user->tariff === TelegramUserTariffEnum::FREE) {

            $user->update([
                'tariff' => TelegramUserTariffEnum::PAID,
                'status' => TelegramUserStatusEnum::ACTIVE,
                'last_payment_date' => now(),
                'next_payment_date' => now()->addMonth(),
            ]);
        }
        // Send a message to the user that the receipt has been approved
        Telegram::sendMessage([...Menu::profile($user->user_id)]);
        Telegram::sendMessage(Menu::receiptApproved($user));
        Telegram::sendMessage([...Menu::base(), ...['chat_id' => $user->user_id]]);

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
