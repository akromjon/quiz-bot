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

        $update = $telegram::getWebhookUpdate();

        $telegram::sendChatAction([
            'chat_id' => $update->getChat()->getId(),
            'action' => 'typing'
        ]);
        
        $class = match ($update->objectType()) {
            'message' => MessageFSM::class,
            'callback_query' => CallbackQueryFSM::class,
            default => null,
        };

        if ($class === null) {

            Log::error('Unknown message type returned');

            return $this->respondSuccess();
        }                    

        $class::handle($telegram, $update);

        return $this->respondSuccess();
    }
}
