<?php

namespace App\Http\Controllers\Telegram;


use Telegram\Bot\Laravel\Facades\Telegram;
use App\Telegram\FSM\CallbackQueryFSM;
use Illuminate\Support\Facades\Log;
use App\Telegram\FSM\MessageFSM;
use Illuminate\Http\JsonResponse;

class TelegramBotController extends TelegramBotBaseController
{
    public function handleWebhook(): JsonResponse
    {
        $update = Telegram::commandsHandler(true);

        match ($update->objectType()) {
            'message' => MessageFSM::handle(),
            'callback_query' => CallbackQueryFSM::handle(),
            default => Log::error('Unknown message type returned'),
        };

        return $this->respondSuccess();
    }
}
