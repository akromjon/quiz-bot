<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class TelegramBotBaseController extends Controller
{
    protected Telegram $telegram;
    protected string $chat_id;
    protected string|null $message;
    protected $callback_query;
    public function __construct(Telegram $telegram)
    {
        $this->telegram = $telegram;
    }
    protected function respondSuccess()
    {
        return response()->json(['status' => 'ok'], 200);
    }

    protected function getWebhookUpdate(Telegram $telegram): Update
    {   // Get the update from Telegram
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
                $this->callback_query = $update->getCallbackQuery();
                $this->message = $update->getCallbackQuery()->getData();
                $this->chat_id = $update->getCallbackQuery()->getMessage()->getChat()->getId();
                return 'callback_query';
            },
            default => 'unknown',
        };

        return 'unknown' !== $type ? $type() : 'uknown';
    }
}
