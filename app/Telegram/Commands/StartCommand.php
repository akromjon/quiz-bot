<?php

namespace App\Telegram\Commands;

use App\Models\TelegramUser;
use App\Telegram\Helpers\Helpers;
use App\Telegram\Menu\Menu;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;
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

        $reply_markup = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button(Menu::get('free_tests')),
                Keyboard::button(Menu::get('general_tests')),
                Keyboard::button(Menu::get('classes'))
            ])
            ->row([
                Keyboard::button(Menu::get('admin')),
                Keyboard::button(Menu::get('help'))
            ])
            ->row([
                Keyboard::button(Menu::get('tariffs'))
            ]);

        $this->replyWithMessage([
            'text' => $user->first_name . ', Botga Xush Kelibsiz!',
            'reply_markup' => $reply_markup
        ]);
    }

    
}
