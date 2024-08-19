<?php

namespace App\Http\Controllers;

use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

abstract class Controller
{
    protected Telegram $telegram;
    protected string $chat_id;
    protected string $message;
    protected $callbackQuery;
    public function __construct(Telegram $telegram)
    {
        $this->telegram = $telegram;
    }
    protected function respondSuccess()
    {
        return response()->json(['status' => 'ok'], 200);
    }

    protected function sendMessage(array $params): void
    {
        $params['chat_id'] = $this->chat_id;

        $this->telegram::sendMessage($params);
    }

    protected function init(Telegram $telegram): Update
    {
        // Handle incoming commands
        $telegram::commandsHandler(true);

        // Get the update from Telegram
        return $telegram::getWebhookUpdate();
    }

    protected function objectType(Update $update): string
    {
        $type = match ($update->objectType()) {
            'message' => function () use ($update) {
                $this->message = $update->getMessage()->getText();
                $this->chat_id = $update->getMessage()->getChat()->getId();
                return 'message';
            },
            'callback_query' => function () use ($update) {
                $this->callbackQuery = $update->getCallbackQuery();
                $this->message = $update->getCallbackQuery()->getData();
                $this->chat_id = $update->getCallbackQuery()->getMessage()->getChat()->getId();
                return 'callback_query';
            },
            default => 'unknown',
        };


        return $type();
    }
}
