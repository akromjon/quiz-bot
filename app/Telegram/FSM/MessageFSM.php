<?php

namespace App\Telegram\FSM;

use App\Models\TelegramUser;
use App\Telegram\Menu\Menu;

class MessageFSM extends Base
{
    protected int $message_id;

    public static function handle(...$params): self
    {
        $ins = new self(...$params);

        $ins->run();

        return $ins;
    }

    public function run(): void
    {
        $this->message = $this->update->getMessage()->getText();

        $this->chat_id = $this->update->getMessage()->getChat()->getId();

        $this->message_id = $this->update->getMessage()->message_id;

        $this->route();
    }
    public function route(): void
    {
        if (
            in_array($this->message, [
                'StartCommand',
                '/start',
                '/help',
                'HelpCommand',
                '/admin',
                'AdminCommand',
                '/profile',
                'ProfileCommand',
            ])
        ) {

            return;
        }

        match ($this->message) {

            '📚 Mavzulashtirilgan Testlar' => $this->sendMessage([
                'text' => '📚 Mavzulashtirilgan Testlar:',
                'reply_markup' => Menu::category()
            ]),

            '👨‍💻 Admin' => $this->handleAdmin(),

            '👤 Mening Profilim' => $this->handleProfile(),

            '🤔 Bot Qanday Ishlaydi?' => $this->handleHowBotWorks(),

            default => $this->sendMessage(['text' => 'Hozirda Bu boyicha ishlamoqdamiz...!']),
        };
    }

    protected function handleAdmin(): void
    {
        $username = setting('admin_username') ?? '@akrom_n';

        $text = "<b>Assalomu alaykum, Botdan foydalanganiz uchun minnatdormiz ☺️, agar sizda savollar yoki takliflar bo'lsa, marhamat bizga yozishingiz mumkin.\n\nAdmin bilan bog'lanish: {$username}</b>";

        $this->sendMessage([
            'text' => $text,
            'reply_markup' => Menu::admin(),
            'parse_mode' => 'HTML',
        ]);
    }

    protected function handleProfile(): void
    {
        $user = TelegramUser::where('user_id', $this->chat_id)->first();

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

        $this->sendMessage([
            'text' => $text,
            'reply_markup' => Menu::profile(),
            'parse_mode' => 'Markdown',
        ]);
    }

    protected function handleHowBotWorks(): void
    {
        $text = setting('how_bot_works') ?? 'Bot qanday ishlaydi?';

        $this->sendMessage([
            'text' => $text,
            'reply_markup' => Menu::how_bot_works(),
            'parse_mode' => 'HTML',
        ]);
    }


}
