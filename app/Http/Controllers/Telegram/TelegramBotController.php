<?php

namespace App\Http\Controllers\Telegram;


use Telegram\Bot\Laravel\Facades\Telegram;
use App\Telegram\FSM\CallbackQueryFSM;
use App\Telegram\FSM\CommandFSM;
use App\Telegram\FSM\FileFSM;
use Illuminate\Support\Facades\Log;
use App\Telegram\FSM\MessageFSM;
use Illuminate\Http\JsonResponse;



class TelegramBotController extends TelegramBotBaseController
{
    public function handleWebhook(): JsonResponse
    {
        $type = $this->objectType(getWebhookUpdate());

        match ($type) {
            'message' => MessageFSM::handle($type),
            'callback_query' => CallbackQueryFSM::handle($type),
            'file' => FileFSM::handle($type),
            'command' => CommandFSM::handle($type),
            default => Log::error('Unknown message type returned'),
        };

        return $this->respondSuccess();
    }
}
