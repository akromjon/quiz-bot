<?php

namespace App\Telegram\Commands;

use App\Models\TelegramUser;
use App\Telegram\Helpers\Helpers;
use App\Telegram\Menu\Menu;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class StartCommand extends Command
{
    use Helpers;
    protected string $name = 'start';
    protected string $description = 'Testlarni Boshlash';

    public function handle()
    {
        $this->typing();

        $update = Telegram::getWebhookUpdate();

        $user = TelegramUser::syncUser($update->getChat());       

        $this->replyWithMessage([
            'text' => $user->first_name . ', Botga Xush Kelibsiz!',
            'reply_markup' => Menu::base()
        ]);
    }

    
}
