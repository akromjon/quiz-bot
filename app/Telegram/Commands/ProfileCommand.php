<?php

namespace App\Telegram\Commands;

use App\Telegram\Menu\Menu;
use Telegram\Bot\Commands\Command;

class ProfileCommand extends Command
{
    protected string $name = 'profile';
    protected string $description = '👤 Mening Profilim';

    public function handle(): void
    {
        $this->replyWithMessage(Menu::profile((int) $this->update->getChat()->get('id')));
    }


}
