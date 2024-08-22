<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class TelegramBotBaseController extends Controller
{
    protected Telegram $telegram;
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

   
}
