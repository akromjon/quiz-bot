<?php

namespace App\Http\Controllers;

use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

abstract class Controller
{
    protected Telegram $telegram;
    protected string $chat_id;
    protected string $message;
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
        if (array_key_exists('reply_markup', $params)) {

            $this->telegram::sendMessage([
                'chat_id' => $this->chat_id,
                'text' => $params['text'],
                'reply_markup' => $params['reply_markup'],
            ]);

            return;
        }

        $this->telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => $params['text'],
        ]);
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
                $this->message = $update->getCallbackQuery()->getData();
                $this->chat_id = $update->getCallbackQuery()->getMessage()->getChat()->getId();
                return 'callback_query';
            },
            default => 'unknown',
        };

        return $type();
    }
}
