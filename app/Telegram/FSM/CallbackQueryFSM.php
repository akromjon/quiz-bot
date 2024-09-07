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
            default => Log::error('Unknown CallbackQuery type returned'),
        };
    }

    protected function handleCategory(): void
    {
        $this->answerCallbackQuery([
            'text' => '📚 Sinflar 📚',
        ]);

        $this->editMessageText(Menu::category());
    }

    protected function handleSubCategory(object $message): void
    {
        $menu = Menu::subcategory($message->id);

        if (array_key_exists('answerCallbackText', $menu)) {

            $this->answerCallbackQuery([
                'text' => $menu['answerCallbackText'],
            ]);

        }

        $this->editMessageText($menu);
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

        if ($menu === null) {

            $this->answerCallbackQuery([
                'text' => Category::find($message->c)->title,
            ]);

            $this->editMessageText(Menu::subcategory($message->c));

            return;
        }

        if (array_key_exists('answerCallbackText', $menu)) {

            $this->answerCallbackQuery([
                'text' => $menu['answerCallbackText'],
            ]);

        }

        $this->editMessageText($menu);

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

        $this->editMessageText($menu);
    }


}
