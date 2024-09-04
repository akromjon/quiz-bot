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
        $update = Telegram::getWebhookUpdate();

        $user = TelegramUser::syncUser($update->getChat());
        
        $user->update([
            'status'=>'active'
        ]);

        $text=setting('welcome_message') ?? "Assalomu alaykum, <a href='tg://user?id={$user->user_id}'>{$user->first_name}</a>.\nBotga Xush Kelibsiz!";

        $text = str_replace(['GET_USER_ID', 'GET_FIRST_NAME'], [$user->user_id, $user->first_name], $text);        

        $this->replyWithMessage([
            'text' => $text,
            'reply_markup' => Menu::base(),
            'parse_mode' => 'HTML'
        ]);
    }

    
}
