<?php

namespace App\Telegram\FSM;

use Illuminate\Support\Facades\Log;
use App\Telegram\Menu\Menu;
use App\Telegram\Middleware\CheckUserIsPaidOrNotMiddleware;

class MessageFSM extends Base
{
    public function route(): void
    {
        $lets_check = CheckUserIsPaidOrNotMiddleware::handle($this->message);

        if (!$lets_check) {

            $this->sendMessage(Menu::handleUnpaidService());

            return;
        }

        match ($this->message) {
            '📚 Mavzulashtirilgan Testlar' => $this->sendMessage(Menu::category()),
            '🧩 Mix Testlar' => $this->sendMessageOrFile(Menu::handeMixQuiz()),
            '🆓 Bepul Testlar' => $this->sendMessageOrFile(Menu::handleFreeQuiz()),
            default => Log::error('Unknown message type returned'),
        };
    }


}
