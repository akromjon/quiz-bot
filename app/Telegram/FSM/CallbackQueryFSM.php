<?php

namespace App\Telegram\FSM;

use App\Models\Category;
use App\Telegram\Menu\Menu;
use Illuminate\Support\Facades\Log;

class CallbackQueryFSM extends Base
{
    protected function route(): void
    {
        match ($this->message->m) {
            'base' => $this->base(),
            'C' => $this->handleCategory(),
            'S' => $this->handleSubCategory($this->message),
            'Q' => $this->handleQuestion($this->message),
            'P' => $this->handlePreviousQuestion($this->message),
            'W' => $this->answerCallbackQuery(Menu::handleWrongAnswer()),
            'M' => $this->handleMixQuiz(),
            'F' => $this->handleFreeQuiz($this->message),
            'FP' => $this->handleFreeQuiz($this->message, false),
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

    protected function handlePreviousQuestion(object $message): void
    {
        $menu = Menu::handlePreviousQuestion($message->c, $message->s, $message->q);

        $this->deleteMessage([
            'message_id' => $this->message_id,
        ]);

        if ($menu === null) {

            $this->answerCallbackQuery([
                'text' => Category::find($message->c)->title,
            ]);

            $this->sendMessage(Menu::subcategory($message->c));

            return;
        }

        if (array_key_exists('answerCallbackText', $menu)) {

            $this->answerCallbackQuery([
                'text' => $menu['answerCallbackText'],
            ]);

        }

        $this->sendMessageOrFile($menu);

    }
    protected function handleQuestion(object $message): void
    {
        $menu = Menu::question(
            category_id: $message->c,
            sub_category_id: $message->s,
            question_id: property_exists($message, 'q') ? $message->q : null,
            load_next: property_exists($message, 'q')
        );

        if (array_key_exists('answerCallbackText', $menu)) {

            $this->answerCallbackQuery([
                'text' => $menu['answerCallbackText'],
            ]);
        }

        $this->deleteMessage([
            'message_id' => $this->message_id,
        ]);

        $this->sendMessageOrFile($menu);
    }

    protected function handleMixQuiz(): void
    {
        $this->answerCallbackQuery([
            'text' => '🧩 Mix Testlar',
        ]);

        $this->deleteMessage([
            'message_id' => $this->message_id,
        ]);

        $menu = Menu::handeMixQuiz();

        $this->sendMessageOrFile($menu);

    }

    protected function handleFreeQuiz(object $message, bool $load_next = true): void
    {
        $this->answerCallbackQuery([
            'text' => '🆓 Bepul Testlar',
        ]);

        $this->deleteMessage([
            'message_id' => $this->message_id,
        ]);

        $menu = Menu::handleFreeQuiz($message->q, $load_next);

        $this->sendMessageOrFile($menu);
    }





}
