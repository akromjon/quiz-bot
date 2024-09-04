<?php

namespace App\Telegram\Commands;

use App\Models\TelegramUser;
use App\Telegram\Helpers\Helpers;
use App\Telegram\Menu\Menu;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class HelpCommand extends Command
{
    use Helpers;
    protected string $name = 'help';
    protected string $description = '🤔 Bot Qanday Ishlaydi?';

    public function handle()
    {
        $this->replyWithMessage([
            'text' => setting('how_bot_works') ?? 'Bot qanday ishlaydi?',
            'reply_markup' => Menu::how_bot_works(),
            'parse_mode' => 'HTML'
        ]);
    }


}
