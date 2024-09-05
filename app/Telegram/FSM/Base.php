<?php

namespace App\Telegram\FSM;

use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

abstract class Base
{
    protected string $chat_id;

    protected int|null $message_id;

    protected string|null|object $message;

    protected Update $update;

    abstract public static function handle(): self;
    abstract protected function route(): void;
    abstract public function run(): void;

    protected function __construct()
    {
        $this->update = Telegram::getWebhookUpdate();
    }

    protected function sendMessage(array $params): void
    {
        $params['chat_id'] = $this->chat_id;

        Telegram::sendMessage($params);
    }


    protected function editMessageText(array $params): void
    {
        $params['chat_id'] = $this->chat_id;

        $params['message_id'] = $this->message_id;

        Telegram::editMessageText($params);
    }

    protected function answerCallbackQuery(array $params): void
    {
        $params['callback_query_id'] = $this->update->getCallbackQuery()->getId();

        Telegram::answerCallbackQuery($params);
    }

    protected function deleteMessage(array $params): void
    {
        $params['chat_id'] = $this->chat_id;

        Telegram::deleteMessage($params);
    }





}
