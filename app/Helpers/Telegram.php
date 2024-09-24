<?php

use App\Models\TelegramUser;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

if (!function_exists('getWebhookUpdate')) {
    function getWebhookUpdate(): Update
    {
        return Telegram::getWebhookUpdate();
    }
}

if (!function_exists('currentTelegramUser')) {
    function currentTelegramUser(): ?TelegramUser
    {
        return TelegramUser::getCurrentUser();
    }
}

if (!function_exists('checkFileType')) {
    function checkFileType(?string $file): ?string
    {
        $file = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        if (in_array($file, ['jpg', 'jpeg', 'png', 'gif']))
            return 'photo';

        if (in_array($file, ['mp4', 'avi', 'mkv', 'mov']))
            return 'video';

        return null;
    }
}
