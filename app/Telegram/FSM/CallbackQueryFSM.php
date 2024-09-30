<?php

namespace App\Telegram\FSM;

use App\Models\Category;
use App\Telegram\Menu\FreeQuestionMenu;
use App\Telegram\Menu\Menu;
use App\Telegram\Menu\MixQuestionMenu;
use App\Telegram\Menu\QuestionMenu;
use App\Telegram\Middleware\CheckUserIsPaidOrNotMiddleware;
use App\Telegram\Middleware\QuestionHistoryMiddleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CallbackQueryFSM extends Base
{
    protected function route(): void
    {

        $lets_check = CheckUserIsPaidOrNotMiddleware::handle($this->message->m);

        if (!$lets_check) {

            $this->sendMessage(Menu::handleUnpaidService());

            return;
        }


        match ($this->message->m) {
            'base' => $this->base(),
            'C' => $this->handleCategory(), // Category
            'S' => $this->handleSubCategory($this->message), // SubCategory
            'Q' => $this->questionHandler($this->message),  // Question
            'W' => $this->sendMessageOrFile(QuestionMenu::handleWrongAnswer($this->message)),
            'M' => $this->handleMixQuiz(), // Mix Quiz
            'F' => $this->handleFreeQuiz($this->message), // Free Quiz
            'FW' => $this->answerCallbackQuery(FreeQuestionMenu::handleWrongAnswer()),
            'R' => $this->handleQuestionReset($this->message),
            default => Log::error('Unknown CallbackQuery type returned'),
        };
    }

    protected function handleCategory(): void
    {
        $this->answerCallbackQuery([
            'text' => '📚 Sinflar 📚',
        ]);

        $this->deleteMessage([
            'message_id' => $this->message_id,
        ]);

        $this->sendMessage(Menu::category());
    }

    protected function handleSubCategory(object $message): void
    {
        $menu = Menu::subcategory($message->id);

        if (array_key_exists('answerCallbackText', $menu)) {

            $this->answerCallbackQuery([
                'text' => $menu['answerCallbackText'],
            ]);

        }

        $this->deleteMessage([
            'message_id' => $this->message_id,
        ]);

        $this->sendMessage($menu);
    }

    protected function base(): void
    {
        $this->deleteMessage([
            'message_id' => $this->message_id,
        ]);

        $this->answerCallbackQuery([
            'text' => '🏠 Asosiy Menu',
        ]);

        $this->sendMessage(Menu::base());
    }


    protected function questionHandler(object $message): void
    {
        $menu = $this->checkIfUserHasHistory($message);

        if (array_key_exists('answerCallbackText', $menu)) {

            $this->answerCallbackQuery([
                'text' => $menu['answerCallbackText'],
            ]);
        }

        $this->deleteMessage([
            'message_id' => $this->message_id,
        ]);

        // $this->handleUserHistory();

        $this->sendMessageOrFile($menu);
    }

    private function checkIfUserHasHistory(object $message): array
    {
        $history = currentTelegramUser()->getHistory($message->s);

        if ($history !== null && $history->page_number !== null && property_exists($message,'h') && $message->h === 'l') {

            return Menu::userHasHistory(history: $history);

        }

        return QuestionMenu::get(
            sub_category_id: $message->s,
            page_number: $message->p,
        );
    }

    private function handleUserHistory(string $model = 'Q'): void
    {

        $inline_keyboard_array = json_decode($this->user_message->get('reply_markup')->inline_keyboard)[0];

        $correct_answer = '';

        foreach ($inline_keyboard_array as $keyboard) {

            if (!in_array($keyboard->text, ['a', 'b', 'c', 'd'])) {
                break;
            }

            $callback_data = json_decode($keyboard->callback_data);

            if (is_object($callback_data) && property_exists($callback_data, 'm') && $callback_data->m === $model) {
                $correct_answer = $keyboard->text . ')';
            }
        }

        if ('' !== $correct_answer) {

            $text = $this->user_message->get('text');

            $text = Str::replace($correct_answer, "✅ {$correct_answer}", $text);

            $this->sendMessageOrFile([
                'type' => 'message',
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);
        }


    }
    protected function handleMixQuiz(): void
    {
        $this->answerCallbackQuery([
            'text' => '🧩 Mix Testlar',
        ]);

        $this->deleteMessage([
            'message_id' => $this->message_id,
        ]);

        $menu = MixQuestionMenu::get();

        $this->handleUserHistory('M');

        $this->sendMessageOrFile($menu);

    }

    protected function handleFreeQuiz(object $message): void
    {
        $this->answerCallbackQuery([
            'text' => '🆓 Bepul Testlar',
        ]);

        $this->deleteMessage([
            'message_id' => $this->message_id,
        ]);

        $menu = FreeQuestionMenu::get($message->p);

        $this->sendMessageOrFile($menu);
    }

    protected function handleQuestionReset(object $message): void
    {
        $menu = QuestionMenu::get(
            sub_category_id: $message->s,
            page_number: 1,
        );

        $this->answerCallbackQuery([
            'text' => '🔄 Testni qayta boshlash',
        ]);

        $this->deleteMessage([
            'message_id' => $this->message_id,
        ]);

        $this->sendMessage($menu);
    }


}
