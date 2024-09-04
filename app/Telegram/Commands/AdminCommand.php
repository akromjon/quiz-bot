<?php

namespace App\Telegram\Commands;

use App\Models\TelegramUser;
use App\Telegram\Helpers\Helpers;
use App\Telegram\Menu\Menu;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class AdminCommand extends Command
{
    use Helpers;
    protected string $name = 'admin';
    protected string $description = '👨‍💻 Admin bilan bog\'lanish';

    public function handle()
    {
        $username = setting('admin_username') ?? '@akrom_n';

        $text = "<b>Assalomu alaykum, Botdan foydalanganiz uchun minnatdormiz ☺️, agar sizda savollar yoki takliflar bo'lsa, marhamat bizga yozishingiz mumkin.\n\nAdmin bilan bog'lanish: {$username}</b>";

        $this->replyWithMessage([
            'text' => $text,
            'reply_markup' => Menu::admin(),
            'parse_mode' => 'HTML'
        ]);
    }


}
