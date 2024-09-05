<?php

namespace App\Telegram\FSM;

use App\Models\Category;
use App\Telegram\Menu\Menu;


class CallbackQueryFSM extends Base
{
    public static function handle(): self
    {
        $instance = new self();

        $instance->run();

        return $instance;
    }

    public function run(): void
    {
        $this->message = json_decode($this->update->getCallbackQuery()->getData());

        if (($this->message === null) || (is_object($this->message) && !property_exists($this->message, 'm'))) {

            $this->editMessageText(Menu::category());

            return;
        }

        $this->chat_id = $this->update->getCallbackQuery()->getMessage()->getChat()->getId();

        $this->message_id = $this->update->getCallbackQuery()->getMessage()->message_id;

        $this->route();
    }
    protected function route(): void
    {
        // we need to check if the message is null and if it has the property 'm' to avoid errors

        match ($this->message->m) {
            'base' => $this->base(),
            'C' => $this->handleCategory(),
            'S' => $this->handleSubCategory($this->message),
            'Q' => $this->handleQuestion($this->message),
            'P' => $this->handlePreviousQuestion($this->message),
            'W' => $this->answerCallbackQuery(Menu::handleWrongAnswer()),
            default => $this->editMessageText(['text' => 'Hozirda Bu boyicha ishlamoqdamiz ...!']),
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
