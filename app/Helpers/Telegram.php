<?php



use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

if (!function_exists('getWebhookUpdate')) {
    function getWebhookUpdate(): Update
    {
        return Telegram::getWebhookUpdate();
    }
}