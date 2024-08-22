<?php

namespace App\Telegram\FSM;

use App\Telegram\Menu\Menu;

class MessageFSM extends Base
{
    protected string $message;

    protected int $message_id;

    public static function handle(...$params): self
    {
        $ins = new self(...$params);

        $ins->run();

        return $ins;
    }

    public function run(): void
    {
        $this->message = $this->update->getMessage()->getText();
       
        $this->chat_id = $this->update->getMessage()->getChat()->getId();

        $this->message_id=$this->update->getMessage()->message_id;

        $this->route();
    }
    public  function route(): void
    {
        if (in_array($this->message, ['StartCommand', '/start'])) {

            return;
        }

        match ($this->message) {

            'Sinflar' => $this->sendMessage([
                'text' => 'Sinflar:',
                'reply_markup' => Menu::category()
            ]),

            default => $this->sendMessage(['text' => 'Hozirda Bu boyicha ishlamoqdamiz...!']),
        };
    }

   
}
