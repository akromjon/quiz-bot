<?php

namespace App\Http\Controllers\Telegram;

use App\Telegram\FSM\CallbackQueryFSM;
use App\Telegram\FSM\MessageFSM;
use Illuminate\Support\Facades\Log;

class TelegramBotController extends TelegramBotBaseController
{
    public function handleWebhook()
    {
        $telegram = $this->telegram;

        $telegram::commandsHandler(true);

        $update = $this->getWebhookUpdate($telegram);

        $type = $this->objectType($update);

        if ('uknown' === $type) {

            Log::error('uknown message type is returned');

            return $this->respondSuccess();
        }

        if ("StartCommand" === $this->message || "/start" === $this->message) {

            return $this->respondSuccess();
        }

        match ($type) {

            'message' => MessageFSM::handle($telegram, $this->message, $this->chat_id, $this->callback_query),

            'callback_query' => CallbackQueryFSM::handle($telegram, $this->message, $this->chat_id, $this->callback_query),
        };

        return $this->respondSuccess();
    }
}
