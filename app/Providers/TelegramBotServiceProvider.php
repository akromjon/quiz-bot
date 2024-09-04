<?php

namespace App\Providers;

use App\Telegram\Commands\AdminCommand;
use App\Telegram\Commands\HelpCommand;
use App\Telegram\Commands\ProfileCommand;
use Illuminate\Support\ServiceProvider;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Telegram\Commands\StartCommand;

class TelegramBotServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Telegram::addCommands([
            StartCommand::class,
            ProfileCommand::class,
            AdminCommand::class,
            HelpCommand::class,
        ]);
    }

    public function register()
    {
        //
    }
}
