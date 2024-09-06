<?php

namespace App\Telegram\Commands;

use App\Models\TelegramUser;
use App\Telegram\Menu\Menu;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class StartCommand extends Command
{
    protected string $name = 'start';
    protected string $description = 'Testlarni Boshlash';

    public function handle(): void
    {
        $update = Telegram::getWebhookUpdate();

        $user = TelegramUser::createOrUpdate($update->getChat(),true);

        $this->replyWithMessage(Menu::base($user));
    }


}
