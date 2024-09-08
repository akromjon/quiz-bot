<?php

namespace App\Telegram\Menu;

use App\Models\Category;
use App\Models\Enums\TelegramUserTariffEnum;
use App\Models\Question;
use App\Models\SubCategory;
use App\Models\TelegramUser;
use App\Telegram\FSM\FileFSM;
use Telegram\Bot\Keyboard\Keyboard;

class Menu
{
    /**
     * @param string $model, string $id
     * @return array <string, string>
     */
    private static function getCallbackData(string $model, string $id): array
    {
        return [
            'm' => class_basename($model)[0],
            'id' => $id,
        ];
    }

    private static function getBackHomeButtons(array $callback_data = ['m' => 'base', 'id' => '']): array
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

    private static function makeInlineKeyboard(): Keyboard
    {
        return Keyboard::make()
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);
    }

    private static function makeKeyboardButton(): Keyboard
    {
        return Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);
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
                    'text' => '📤 Admin',
                    'url' => setting('admin_username_link') ?? "https://t.me/akrom_n",
                ]),
            ]);

        return [
            'text' => $text,
            'reply_markup' => $keyboard,
            'parse_mode' => 'HTML',
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
                    'text' => '📤 Admin',
                    'url' => setting('admin_username_link') ?? "https://t.me/akrom_n",
                ]),
            ]);

        $username = setting('admin_username') ?? '@akrom_n';

        $text = "<b>Assalomu alaykum, Botdan foydalanganiz uchun minnatdormiz ☺️, agar sizda savollar yoki takliflar bo'lsa, marhamat bizga yozishingiz mumkin.\n\nAdmin bilan bog'lanish: {$username}</b>";


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
                Keyboard::button(['text' => '🆓 Bepul Testlar']),
                Keyboard::button('🧩 Mix Testlar'),
            ])
            ->row([
                Keyboard::button('📚 Mavzulashtirilgan Testlar'),
            ]);
        // ->row([
        //     // Keyboard::button('🤔 Bot Qanday Ishlaydi?'),
        //     Keyboard::button('ℹ️ Biz Haqimizda')
        // ])
        // ->row([
        //     // Keyboard::button('👤 Mening Profilim'),
        //     // Keyboard::button('👨‍💻 Admin'),
        // ]);

        return [
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
                    'text' => $c->title,
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
        $subcategories = SubCategory::active()->where('category_id', $category_id)->get();

        $callback_data = [
            'm' => 'Q',
            'c' => $category_id,
        ];

        $keyboard = self::makeInlineKeyboard();

        foreach ($subcategories as $c) {

            $callback_data['s'] = $c->id;

            $keyboard->row([
                Keyboard::inlineButton([
                    'text' => $c->title.": 🔒 ". $c->questions->count() . " ta test",
                    'callback_data' => json_encode($callback_data),
                ]),
            ]);
        }

        $callback_data = self::getCallbackData(Category::class, $category_id);

        $keyboard->row(self::getBackHomeButtons($callback_data));



        return [
            'text' => Category::find($category_id)->title,
            'reply_markup' => $keyboard,
            'answerCallbackText' => $subcategories?->first()?->category?->title,
            'parse_mode' => 'HTML',
        ];
    }



    protected static function getNextQuestion(int $sub_category_id, int|null $question_id)
    {
        $query = Question::where('sub_category_id', $sub_category_id)
            ->active()
            ->orderBy('id');

        if ($question_id !== null) {

            $query->where('id', '>', $question_id);
        }

        return $query->first();
    }



    public static function question(int $category_id, int $sub_category_id, int $question_id = null, bool $load_next = false): array
    {

        $question = self::getNextQuestion($sub_category_id, $question_id);

        if (!$question) {

            return self::handleWhenThereIsNoQuestion($category_id);
        }

        return self::handleQuestion(question: $question, sub_category_id: $sub_category_id, category_id: $category_id, load_next: $load_next);
    }

    public static function handlePreviousQuestion(int $category_id, int $sub_category_id, int $question_id): array|null
    {
        $question = Question::where('sub_category_id', $sub_category_id)
            ->active()
            ->where('id', '<', $question_id)
            ->orderByDesc('id')
            ->first();


        if (!$question) {

            return null;
        }

        $keyboard = self::handleQuestion($question, $sub_category_id, $category_id, true);

        $keyboard['answerCallbackText'] = '⬅️ Orqaga';

        return $keyboard;
    }



    protected static function handleQuestion(Question $question, int $sub_category_id, int $category_id, bool $load_next): array
    {
        $callback_data = [
            's' => $sub_category_id,
            'c' => $category_id,
            'q' => $question->id,
        ];

        $letters = ['a', 'b', 'c', 'd', 'e', 'f', 'g'];

        $keyboards = [];

        foreach ($question->questionOptions as $key => $option) {

            $callback_data['id'] = $option->id;

            $callback_data['m'] = $option->is_answer ? 'Q' : 'W';

            $keyboards[] = Keyboard::inlineButton([
                'text' => $letters[$key],
                'callback_data' => json_encode($callback_data),
            ]);
        }

        // $callback_data = self::getCallbackData(SubCategory::class,(string)$sub_category_id);   

        $callback_data = [
            'm' => 'P',
            'c' => $category_id,
            's' => $sub_category_id,
            'q' => $question->id,
        ];


        $keyboard = self::makeInlineKeyboard()
            ->row($keyboards)
            ->row([
                Keyboard::inlineButton([
                    'text' => '⬅️ Orqaga',
                    'callback_data' => json_encode($callback_data),
                ])
            ])
            ->row([
                Keyboard::inlineButton([
                    'text' => '🏁 Testni Tugatish',
                    'callback_data' => json_encode(['m' => 'base', 'id' => '']),
                ])
            ]);



        return [
            'type' => $load_next ? 'edit_message' : 'message',
            'reply_markup' => $keyboard,
            'parse_mode' => 'HTML',
            'text' => self::formatQuestion($question),
            'answerCallbackText' => $load_next ? "To'g'ri ✅" : '🤞 Omad 🤞'
        ];
    }

    private static function formatQuestion(Question $question): string
    {
        $sub_category = $question->subCategory;

        $text = <<<TEXT
            <b>{$sub_category->category->trimmed_title}, {$sub_category->title}</b>\n        
            {$question->number}/{$sub_category->questions->count()} - SAVOL:
            {$question->question}\n\n
            TEXT;
        $text .= implode("\n", $question->questionOptions->pluck('option')->toArray());

        return $text;
    }

    protected static function handleWhenThereIsNoQuestion(int $category_id): array
    {
        $callback_data = self::getCallbackData(SubCategory::class, $category_id);

        $menu = self::makeInlineKeyboard()
            ->row(self::getBackHomeButtons($callback_data));

        return [
            'type' => 'message',
            'reply_markup' => $menu,
            'parse_mode' => 'HTML',
            'text' => "🏁 Testlar Tugadi 🏁",
            'answerCallbackText' => '🏁 Testlar Tugadi 🏁',
        ];
    }

    public static function handleWrongAnswer(): array
    {
        return [
            'text' => "Noto'g'ri ❌",
            'show_alert' => true,
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
}
