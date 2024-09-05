<?php

namespace App\Telegram\FSM;

use App\Telegram\Menu\Menu;

class MessageFSM extends Base
{
    protected array $excluded_messages = [
        'StartCommand',
        '/start',
        '/help',
        'HelpCommand',
        '/admin',
        'AdminCommand',
        '/profile',
        'ProfileCommand',
    ];

    public static function handle(): self
    {
        $ins = new self;

        $ins->run();

        return $ins;
    }

    public function run(): void
    {
        $this->message = $this->update->getMessage()->getText();

        if (in_array($this->message, $this->excluded_messages)) {
            return;
        }

        $this->chat_id = $this->update->getMessage()->getChat()->getId();

        $this->message_id = $this->update->getMessage()->message_id;

        $this->route();
    }


    public function route(): void
    {
        match ($this->message) {

            '📚 Mavzulashtirilgan Testlar' => $this->sendMessage(Menu::category()),

            '👨‍💻 Admin' => $this->sendMessage(Menu::admin()),

            '👤 Mening Profilim' => $this->sendMessage(Menu::profile($this->chat_id)),

            '🤔 Bot Qanday Ishlaydi?' => $this->sendMessage(Menu::how_bot_works()),

            default => $this->sendMessage(['text' => 'Hozirda Bu boyicha ishlamoqdamiz...?']),
        };
    }

}
