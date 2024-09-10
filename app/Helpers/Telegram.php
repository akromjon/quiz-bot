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