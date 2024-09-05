<?php

namespace App\Telegram\Commands;

use App\Models\TelegramUser;
use App\Telegram\Menu\Menu;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class AdminCommand extends Command
{
    protected string $name = 'admin';
    protected string $description = '👨‍💻 Admin bilan bog\'lanish';

    public function handle(): void
    {
        $this->replyWithMessage(Menu::admin());
    }


}
