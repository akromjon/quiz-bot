<?php

namespace App\Notifications;

use App\Models\Enums\TelegramUserNotificationEnum;
use App\Models\TelegramUser;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\TelegramUserNotification as TelegramUserNotificationModel;
use Illuminate\Database\Eloquent\Collection;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramUserNotification extends Notification
{
    use Queueable;

    public function __construct(protected TelegramUserNotificationModel $telegramUserNotification)
    {

    }


    public function via(object $notifiable): array
    {
        return [TelegramNotificationChannel::class];
    }



    public function toTelegram(TelegramUserNotificationModel $telegramUserNotification): void
    {
        $users = $this->getUsersBasedOnSendTo($telegramUserNotification);

        if ($users===null) {
            return;
        }

        foreach ($users as $user) {
            $keyboards = $this->prepareKeyboards($telegramUserNotification);
            $this->sendMessageOrPhoto($telegramUserNotification, $user, $keyboards);
        }
    }

    private function getUsersBasedOnSendTo(TelegramUserNotificationModel $telegramUserNotification): ?Collection
    {
        return match ($telegramUserNotification->send_to) {
            TelegramUserNotificationEnum::ALL => TelegramUser::all(),
            TelegramUserNotificationEnum::ACTIVE, TelegramUserNotificationEnum::INACTIVE =>TelegramUser::where('status', $telegramUserNotification->send_to)->get(),
            TelegramUserNotificationEnum::FREE, TelegramUserNotificationEnum::PAID =>TelegramUser::where('tariff', $telegramUserNotification->send_to)->get(),
            default => null,
        };
    }

    private function prepareKeyboards(TelegramUserNotificationModel $telegramUserNotification): ?Keyboard
    {
        if (!is_array($telegramUserNotification->params['inlineButtons']) && !empty($telegramUserNotification->params['inlineButtons'])) {
            return null;
        }

        $keyboards = Keyboard::make()
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);

        foreach ($telegramUserNotification->params['inlineButtons'] as $button) {
            $keyboards->row([
                Keyboard::inlineButton([
                    'text' => $button['text'],
                    'callback_data' => $button['callback_data'],
                ])
            ]);
        }

        return $keyboards;
    }

    private function sendMessageOrPhoto(TelegramUserNotificationModel $telegramUserNotification, TelegramUser $user, ?Keyboard $keyboards): void
    {
        match ($telegramUserNotification->params['photo']) {
            null => $this->sendMessage($telegramUserNotification, $user, $keyboards),
            default => $this->sendPhoto($telegramUserNotification, $user, $keyboards),
        };
    }

    private function sendPhoto(TelegramUserNotificationModel $telegramUserNotification, TelegramUser $user, ?Keyboard $keyboards): void
    {
        $params = [
            'photo' => InputFile::create(asset("storage/{$telegramUserNotification->params['photo']}")),
            'caption' => $telegramUserNotification->params['text'],
            'parse_mode' => 'HTML',
            'chat_id' => $user->user_id,

        ];

        if ($keyboards) {
            $params['reply_markup'] = $keyboards;
        }

        Telegram::sendPhoto($params);
    }

    private function sendMessage(TelegramUserNotificationModel $telegramUserNotification, TelegramUser $user, ?Keyboard $keyboards): void
    {
        $params = [
            'text' => $telegramUserNotification->params['text'],
            'parse_mode' => 'HTML',
            'chat_id' => $user->user_id,
        ];

        if ($keyboards) {
            $params['reply_markup'] = $keyboards;
        }

        Telegram::sendMessage($params);
    }


}
