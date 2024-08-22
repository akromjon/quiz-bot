<?php

namespace App\Telegram\FSM;

use App\Telegram\Menu\Menu;

class MessageFSM extends Base
{
    public  function route(): void
    {
        match ($this->message) {

            'Sinflar' => $this->sendMessage([
                'text' => 'Sinflar:',
                'reply_markup' => Menu::category()
            ]),

            default => $this->sendMessage(['text' => 'Hozirda Bu boyicha ishlamoqdamiz...!']),
        };
    }

    public static function handle(...$params): self
    {
        $ins = new self(...$params);

        $ins->route();

        return $ins;
    }
}
