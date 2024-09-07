<?php

namespace App\Telegram\FSM;

use Illuminate\Support\Facades\Log;
use App\Telegram\Menu\Menu;

class MessageFSM extends Base
{
    public function route(): void
    {
        match ($this->message) {

            '📚 Mavzulashtirilgan Testlar' => $this->sendMessage(Menu::category()),           

            default => Log::error('Unknown message type returned'),
        };
    }

}
