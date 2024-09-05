<?php

namespace App\Telegram\Commands;

use App\Telegram\Menu\Menu;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class HelpCommand extends Command
{
    protected string $name = 'help';
    protected string $description = '🤔 Bot Qanday Ishlaydi?';

    public function handle(): void
    {
        $this->replyWithMessage(Menu::how_bot_works());
    }


}
