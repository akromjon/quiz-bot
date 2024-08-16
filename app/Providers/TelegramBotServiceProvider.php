<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Telegram\Commands\StartCommand;

class TelegramBotServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Telegram::addCommands([
            StartCommand::class,
        ]);
    }

    public function register()
    {
        //
    }
}
