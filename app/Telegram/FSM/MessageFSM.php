<?php

namespace App\Telegram\FSM;

use App\Telegram\Menu\FreeQuestionMenu;
use App\Telegram\Menu\MixQuestionMenu;
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
            '🧩 Mix Testlar' => $this->sendMessageOrFile(MixQuestionMenu::get()),
            '🆓 Bepul Testlar' => $this->sendMessageOrFile(FreeQuestionMenu::get(1)),
            '👤 Mening Profilim'=>$this->sendMessage(Menu::profile($this->chat_id)),
            '🤔 Bot qanday ishlaydi?'=>$this->sendMessage(Menu::howBotWorks()),
            default => Log::error('Unknown message type returned from MessageFSM'),
        };
    }



}
