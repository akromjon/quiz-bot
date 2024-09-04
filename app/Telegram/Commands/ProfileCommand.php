<?php

namespace App\Telegram\Commands;

use App\Models\TelegramUser;
use App\Telegram\Helpers\Helpers;
use App\Telegram\Menu\Menu;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class ProfileCommand extends Command
{
    use Helpers;
    protected string $name = 'profile';
    protected string $description = '👤 Mening Profilim';

    public function handle()
    {
        $user = TelegramUser::where('user_id', $this->update->getChat()->get('id'))->first();

        $tarif = $user?->tariff == 'free' ? '🆓 Bepul' : '*💎 Pullik*';

        $next_payment_date = $user?->next_payment_date?->format('d.m.Y') ?? '-';

        $last_payment_date = $user?->last_payment_date?->format('d.m.Y') ?? '-';

        $balance = $user?->balance ?? 0;

        $text = <<<TEXT
        *👤 Profil:*\n
        🪪 ID: `$user?->user_id`
        📝 Ism: {$user?->first_name}
        📅 Qo'shilgan sana: {$user?->created_at->format('d.m.Y')}
        🔋 Tarif Reja: {$tarif}
        💰 Balans: {$balance} so'm
        ⏳ Keyingi to'lov: {$next_payment_date}
        🗓️ Oxirgi to'lov: {$last_payment_date}
        TEXT;

        $this->replyWithMessage([
            'text' => $text,
            'reply_markup' => Menu::profile(),
            'parse_mode' => 'Markdown',
        ]);
    }


}
