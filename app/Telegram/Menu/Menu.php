<?php

namespace App\Telegram\Menu;

use App\Models\Category;
use App\Models\Enums\TelegramUserTariffEnum;
use App\Models\Question;
use App\Models\QuestionHistory;
use App\Models\SubCategory;
use App\Models\TelegramUser;
use App\Telegram\FSM\FileFSM;
use App\Telegram\Middleware\QuestionHistoryMiddleware;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Keyboard\Keyboard;

class Menu
{
    /**
     * @param string $model, string $id
     * @return array <string, string>
     */
    protected static function getCallbackData(string $model, string $id): array
    {
        return [
            'm' => class_basename($model)[0],
            'id' => $id,
        ];
    }

    protected static function getBackHomeButtons(array $callback_data = ['m' => 'base', 'id' => '']): array
    {
        return [
            Keyboard::inlineButton([
                'text' => '🏠 Asosiy Menyu',
                'callback_data' => json_encode(['m' => 'base', 'id' => '']),
            ]),
            Keyboard::inlineButton([
                'text' => '⬅️ Orqaga',
                'callback_data' => json_encode($callback_data),
            ]),
        ];
    }

    protected static function makeInlineKeyboard(): Keyboard
    {
        return Keyboard::make()
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(false);
    }

    protected static function makeKeyboardButton(): Keyboard
    {
        return Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(false);
    }


    public static function howBotWorks(): array
    {
        $text = setting('how_bot_works') ?? 'Bot qanday ishlaydi?';

        $keyboard = self::makeInlineKeyboard()
            ->row([
                Keyboard::inlineButton([
                    'text' => '🏠 Asosiy Menyu',
                    'callback_data' => json_encode(['m' => 'base', 'id' => '']),
                ]),
                Keyboard::inlineButton([
                    'text' => '👨‍💻 Admin',
                    'url' => setting('admin_username_link') ?? "https://t.me/akrom_n",
                ]),
            ]);

        return [
            'text' => $text,
            'reply_markup' => $keyboard,
            'parse_mode' => 'Markdown',
        ];
    }

    public static function profile(int|string $chat_id): array
    {
        $keyboard = self::makeInlineKeyboard()
            ->row([
                Keyboard::inlineButton([
                    'text' => '🏠 Asosiy Menyu',
                    'callback_data' => json_encode(['m' => 'base', 'id' => '']),
                ]),
            ]);

        $user = TelegramUser::where('user_id', $chat_id)->first();

        $tariff = $user->tariff == TelegramUserTariffEnum::FREE ? '🆓 Bepul' : '*💎 Pullik*';

        $payment = $user->tariff == TelegramUserTariffEnum::FREE ? '❌' : '✅';

        $text = <<<TEXT
        *👤 Profil:*\n
        🪪 ID: `$user?->user_id`
        📝 Ism: {$user?->first_name}
        📅 Qo'shilgan sana: {$user?->created_at}
        🔋 Tarif Reja: {$tariff}
        💰 Balans: $user->balance so'm
        💵 To'lov: $payment
        🗓️ Oxirgi to'lov: $user->last_payment_date
        🕔 Keyingi to'lov: $user->next_payment_date
        TEXT;

        return [
            'chat_id' => $chat_id,
            'text' => $text,
            'reply_markup' => $keyboard,
            'parse_mode' => 'Markdown',
        ];
    }

    public static function admin(): array
    {
        $keyboard = self::makeInlineKeyboard()
            ->row([
                Keyboard::inlineButton([
                    'text' => '🏠 Asosiy Menyu',
                    'callback_data' => json_encode(['m' => 'base', 'id' => '']),
                ]),
                Keyboard::inlineButton([
                    'text' => '👨‍💻 Admin',
                    'url' => setting('admin_username_link') ?? "https://t.me/akrom_n",
                ]),
            ]);


        $text = setting('admin_message') ?? 'Admin xabar';


        return [
            'text' => $text,
            'reply_markup' => $keyboard,
            'parse_mode' => 'HTML',
        ];
    }


    public static function base(TelegramUser $telegramUser = null): array
    {
        $text = null;

        if ($telegramUser !== null) {

            $text = setting('welcome_message') ?? "Assalomu alaykum, <a href='tg://user?id={$telegramUser->user_id}'>{$telegramUser->first_name}</a>.\nBotga Xush Kelibsiz!";

            $text = str_replace(['GET_USER_ID', 'GET_FIRST_NAME'], [$telegramUser->user_id, $telegramUser->first_name], $text);

        }

        $keyboard = self::makeKeyboardButton()
            ->row([
                Keyboard::button('🆓 Bepul Testlar'),
                Keyboard::button('🧩 Mix Testlar'),
            ])
            ->row([
                Keyboard::button('📚 Mavzulashtirilgan Testlar'),
            ])
            ->row([
                Keyboard::button('👤 Mening Profilim'),
                Keyboard::button('🤔 Bot qanday ishlaydi?'),
            ]);

        return [
            'type' => 'message',
            'reply_markup' => $keyboard,
            'text' => $text ?? '🏠 Asosiy Menyu 👇',
            'parse_mode' => 'HTML',
        ];
    }

    public static function category(): array
    {

        $categories = Category::getCachedCategories();

        $callback_data = self::getCallbackData(SubCategory::class, '');

        $keyboard = self::makeInlineKeyboard();

        foreach ($categories as $c) {

            $callback_data['id'] = $c->id;

            $keyboard->row([
                Keyboard::inlineButton([
                    'text' => $c->title . ": 🔒 " . $c->questions_count . " ta test",
                    'callback_data' => json_encode($callback_data),
                ]),
            ]);
        }

        $keyboard->row(self::getBackHomeButtons());

        return [
            'reply_markup' => $keyboard,
            'text' => '📚 Sinflar',
            'parse_mode' => 'HTML',
        ];
    }

    public static function subcategory(int $category_id): array
    {

        $subcategories = Cache::rememberForever("sub_categories_{$category_id}", function () use ($category_id) {

            return SubCategory::active()
                ->whereHas('questions')
                ->with('category')
                ->withCount('questions')
                ->where('category_id', $category_id)
                ->get();
        });

        $callback_data = [
            'm' => 'Q',
            'c' => $category_id,
            'p' => 1,
            'h' => 'l'
        ];

        $keyboard = self::makeInlineKeyboard();

        foreach ($subcategories as $c) {

            $callback_data['s'] = $c->id;

            $keyboard->row([
                Keyboard::inlineButton([
                    'text' => $c->title . ": 🔒 " . $c->questions_count . " ta test",
                    'callback_data' => json_encode($callback_data),
                ]),
            ]);
        }

        $callback_data = self::getCallbackData(Category::class, $category_id);

        $keyboard->row(self::getBackHomeButtons($callback_data));



        return [
            'text' => $subcategories?->first()?->category?->title,
            'reply_markup' => $keyboard,
            'answerCallbackText' => $subcategories?->first()?->category?->title,
            'parse_mode' => 'HTML',
        ];
    }
    public static function receipt(): array
    {
        $keyboard = self::makeInlineKeyboard()
            ->row([
                Keyboard::inlineButton([
                    'text' => '🏠 Asosiy Menyu',
                    'callback_data' => json_encode(['m' => 'base', 'id' => '']),
                ]),
            ]);

        return [
            'text' => '🧾 To\'lovni tasdiqlash uchun chek rasmini yoki faylini yuboring 👇',
            'reply_markup' => $keyboard,
            'answerCallbackText' => '🧾 Chekni yuboring',
            'parse_mode' => 'HTML',
        ];
    }

    public static function receiptPending(): array
    {
        return [
            'text' => '🎉 Fayl muvaffaqiyatli yuklandi. Cheklarni ko\'rib chiqish odatda 2-5 daqiqa davom etadi.',
            'parse_mode' => 'HTML',
        ];
    }

    public static function fileTypeNotAllowedMessage(): array
    {
        return [
            'parse_mode' => 'HTML',
            'text' => 'Faqat: <b>' . implode(', ', FileFSM::$allowed_file_types) . '</b> formatdagi fayllarni yuklashingiz mumkin 🤔'
        ];
    }

    public static function fileSizeNotAllowedMessage(): array
    {
        return [
            'parse_mode' => 'HTML',
            'text' => 'Fayl hajmi 20MB dan oshmasligi kerak 🙁'
        ];
    }

    public static function receiptAlreadyExists(): array
    {
        return [
            'text' => 'Sizning hozircha to\'lovni tasdiqlash uchun faylingiz yuborilgan. Iltimos, avvalgi check qarorini kuting.',
            'parse_mode' => 'HTML',
        ];
    }

    public static function receiptApproved(TelegramUser $user): array
    {
        return [
            'chat_id' => $user->user_id,
            'text' => setting('receipt_approved_message') ?? '✅ To\'lov tasdiqlandi. Sizning hisobingizga mablag\' qo\'shildi.',
            'parse_mode' => 'HTML',
        ];
    }

    public static function receiptRejected(TelegramUser $user): array
    {

        return [
            'chat_id' => $user->user_id,
            'text' => setting('receipt_rejected_message') ?? '❌ To\'lov tasdiqlanmadi. Iltimos, qaytadan yuboring.',
            'parse_mode' => 'HTML',
        ];
    }



    protected static function prepareKeyboards(Question $randomQuestion, string $m = 'M', string $w = 'W'): array
    {
        $letters = ['a', 'b', 'c', 'd', 'e', 'f', 'g'];

        $keyboards = [];

        foreach ($randomQuestion->questionOptions as $key => $option) {

            $keyboards[] = Keyboard::inlineButton([
                'text' => $letters[$key],
                'callback_data' => json_encode([
                    'q' => $randomQuestion->id,
                    'm' => $option->is_answer ? $m : $w,
                    'id' => $option->id,
                ]),
            ]);
        }

        return $keyboards;
    }

    protected static function createFinishTestButton(): array
    {
        return [
            Keyboard::inlineButton([
                'text' => '🏁 Testni yakunlash',
                'callback_data' => json_encode(['m' => 'base', 'id' => '']),
            ])
        ];
    }



    public static function handleUnpaidService(): array
    {
        return [
            'text' => setting('unpaid_service_message') ?? 'Bu xizmat faqat pullik foydalanuvchilar uchun mavjud 🤔',
            'parse_mode' => 'HTML',
        ];
    }

    public static function notifyUserWhenBalanceIsNotEnough(int $chat_id): array
    {
        return [
            'chat_id' => $chat_id,
            'text' => 'Balansingizda yetarli mablag\' mavjud emas. Iltimos, balansingizni to\'ldiring.',
            'parse_mode' => 'HTML',
        ];
    }

    public static function notifyUserWhenMoneyIsSubtracted(int $chat_id, int $amount, int $balance): array
    {
        return [
            'chat_id' => $chat_id,
            'text' => "Balansingizdan {$amount} so'm yechildi. Sizning joriy balansingiz: {$balance} so'm",
            'parse_mode' => 'HTML',
        ];
    }

    public static function notifyUserWhenProfileChanged(int $chat_id, int $day): array
    {
        return [
            'chat_id' => $chat_id,
            'text' => "Tarifingiz profilda o'zgardi, testlarni {$day} kun bepul ishlashingiz mumkin!",
            'parse_mode' => 'HTML',
        ];
    }

    public static function termsAndConditions(): array
    {
        $keyboard = self::makeInlineKeyboard()
            ->row([
                Keyboard::inlineButton([
                    'text' => '🏠 Asosiy Menyu',
                    'callback_data' => json_encode(['m' => 'base', 'id' => '']),
                ]),
            ]);

        return [
            'text' => setting('terms_and_conditions') ?? 'Foydalanish shartlari',
            'reply_markup' => $keyboard,
            'parse_mode' => 'Markdown',
        ];
    }

    public static function privacyPolicy(): array
    {
        $keyboard = self::makeInlineKeyboard()
            ->row([
                Keyboard::inlineButton([
                    'text' => '🏠 Asosiy Menyu',
                    'callback_data' => json_encode(['m' => 'base', 'id' => '']),
                ]),
            ]);

        return [
            'text' => setting('privacy_policy') ?? 'Maxfiylik siyosati',
            'reply_markup' => $keyboard,
            'parse_mode' => 'Markdown',
        ];
    }

    public static function userHasHistory(QuestionHistory $history): array
    {
        $text = "Sizda avval boshlangan test bor. Boshidan boshlaysizmi yoki davom etasizmi?";

        $keyboards = self::makeInlineKeyboard()
            ->row([
                Keyboard::inlineButton([
                    'text' => '🔄 Boshidan boshlash',
                    'callback_data' => json_encode([
                        'm' => 'R',
                        's' => $history->sub_category_id,
                        'p' => 1
                    ]),
                ]),
                Keyboard::inlineButton([
                    'text' => '▶️ Davom etish',
                    'callback_data' => json_encode([
                        'm' => 'Q',
                        's' => $history->sub_category_id,
                        'p' => $history->page_number
                    ]),
                ]),
            ]);


        return [
            'type' => 'message',
            'text' => $text,
            'reply_markup' => $keyboards,
        ];
    }

    protected static function formatQuestion(Question $question, int $page_number): string
    {
        $sub_category = $question->subCategory;

        $questions_count = $sub_category->questionCount();

        $text = <<<TEXT
            <b>{$sub_category->category->trimmed_title}, {$sub_category->title}</b>\n
            {$page_number}/{$questions_count} - SAVOL:
            {$question->question}\n\n
            TEXT;
        $text .= implode("\n", $question->questionOptions->pluck('option')->toArray());

        return $text;
    }


}
