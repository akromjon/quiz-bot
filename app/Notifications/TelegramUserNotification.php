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
use Telegram\Bot\Exceptions\TelegramResponseException;
use Illuminate\Support\Facades\Log;


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

        if ($users === null) {
            throw new \Exception('Invalid send_to value');
        }

        foreach ($users as $user) {
            try {

                $keyboards = $this->prepareKeyboards($telegramUserNotification);
                $this->sendMessageOrFile($telegramUserNotification, $user, $keyboards);

            }catch(TelegramResponseException $e) {
                if (403 === $e->getCode()) {

                    $chat_id = null;

                    $data = $e->getResponse()->getRequest()->getParams();

                    Log::error("data:", $data);

                    if(array_key_exists('multipart', $data)) {
                        foreach ($data['multipart'] as $part) {
                            if ($part['name'] === 'chat_id') {
                                $chat_id = $part['contents'];
                                break;
                            }
                        }
                    }

                    if(array_key_exists('form_params', $data)) {
                        $chat_id = $data['form_params']['chat_id'];
                    }

                    if (is_int($chat_id)) {

                        TelegramUser::where('user_id', $chat_id)->update(['status' => 'blocked']);
                    }

                    Log::error("User $chat_id is blocked the bot");

                }
            }

        }
    }

    private function getUsersBasedOnSendTo(TelegramUserNotificationModel $telegramUserNotification): ?Collection
    {
        return match ($telegramUserNotification->send_to) {
            TelegramUserNotificationEnum::ALL => TelegramUser::all(),
            TelegramUserNotificationEnum::ACTIVE, TelegramUserNotificationEnum::INACTIVE => TelegramUser::where('status', $telegramUserNotification->send_to)->get(),
            TelegramUserNotificationEnum::FREE, TelegramUserNotificationEnum::PAID => TelegramUser::where('tariff', $telegramUserNotification->send_to)->get(),
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

            $params = [
                'text' => $button['text'],
            ];

            if (isset($button['url']) && !empty($button['url'])) {
                $params['url'] = $button['url'];
            }

            if (isset($button['callback_data']) && !empty($button['callback_data'])) {
                $params['callback_data'] = $button['callback_data'];
            }

            $keyboards->row([
                Keyboard::inlineButton($params)
            ]);
        }

        return $keyboards;
    }

    private function sendMessageOrFile(TelegramUserNotificationModel $telegramUserNotification, TelegramUser $user, ?Keyboard $keyboards): void
    {

        match (checkFileType($telegramUserNotification->params['file'])) {
            'photo' => $this->sendPhoto($telegramUserNotification, $user, $keyboards),
            'video' => $this->sendVideo($telegramUserNotification, $user, $keyboards),
            default => $this->sendMessage($telegramUserNotification, $user, $keyboards),
        };
    }



    private function sendPhoto(TelegramUserNotificationModel $telegramUserNotification, TelegramUser $user, ?Keyboard $keyboards): void
    {
        $params = [
            'photo' => InputFile::create(asset("storage/{$telegramUserNotification->params['file']}")),
            'caption' => $telegramUserNotification->params['text'],
            'parse_mode' => 'HTML',
            'chat_id' => $user->user_id,

        ];

        if ($keyboards) {
            $params['reply_markup'] = $keyboards;
        }

        Telegram::sendPhoto($params);
    }

    private function sendVideo(TelegramUserNotificationModel $telegramUserNotification, TelegramUser $user, ?Keyboard $keyboards): void
    {
        $params = [
            'video' => InputFile::create(asset("storage/{$telegramUserNotification->params['file']}")),
            'caption' => $telegramUserNotification->params['text'],
            'parse_mode' => 'HTML',
            'chat_id' => $user->user_id,
        ];

        if ($keyboards) {
            $params['reply_markup'] = $keyboards;
        }

        Telegram::sendVideo($params);
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
