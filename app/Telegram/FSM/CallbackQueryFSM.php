<?php

namespace App\Telegram\FSM;

use App\Models\Category;
use App\Models\SubCategory;
use App\Telegram\Menu\Menu;
use Telegram\Bot\Keyboard\Keyboard;

class CallbackQueryFSM extends Base
{
    protected object $update;
    protected object $callback_query;
    protected int $message_id;
    public static function handle(...$params): self
    {
        $instance = new self(...$params);

        $instance->run();

        return $instance;
    }

    public function run(): void
    {

        $this->callback_query = $this->update->getCallbackQuery();

        $this->message = json_decode($this->update->getCallbackQuery()->getData());

        $this->chat_id = $this->update->getCallbackQuery()->getMessage()->getChat()->getId();

        $this->message_id = $this->callback_query->getMessage()->message_id;

        $this->route();
    }
    protected function route(): void
    {
        // we need to check if the message is null and if it has the property 'm'  

        $message = $this->message;

        if ($message === null) {

            $this->editMessageText($this->createEditMessage($this->message_id, 'tanla: ', Menu::category()));

            return;
        }

        if (is_object($message) && !property_exists($message, 'm')) {

            $this->editMessageText($this->createEditMessage($this->message_id, 'tanla: ', Menu::category()));


            return;
        }


        match ($message->m) {
            'base' => $this->handleBase(),
            'C' => $this->handleCategory(),
            'S' =>$this->handleSubCategory($message),
            'Q' => $this->handleQuestion($message),
            'P' => $this->handlePreviousQuestion($message),
            'W' => $this->handleWrongAnswer(),
            default => $this->editMessageText(
                $this->createEditMessage($this->message_id, 'Hozirda Bu boyicha ishlamoqdamiz...!')
            ),
        };
    }

    protected function handleCategory(): void
    {
        $this->answerCallbackQuery([
            'text' => '📚 Sinflar 📚',
        ]);

        $this->editMessageText($this->createEditMessage($this->message_id, '📚 Mavzulashtirilgan Testlar:', Menu::category()));
    }

    protected function handleSubCategory(object $message): void
    {
        $menu = Menu::subcategory($message->id);

        if (array_key_exists('answerCallbackText', $menu)) {

            $this->answerCallbackQuery([
                'text' => $menu['answerCallbackText'],
            ]);
        }

        $this->editMessageText($this->createEditMessage($this->message_id, 'Bo\'limlar: ', $menu['keyboard']));

    }

    protected function handleBase(): void
    {

        $this->deleteMessage([
            'message_id' => $this->message_id,
        ]);

        $this->sendMessage($this->createMessage('Asosiy Menu:', Menu::base()));
    }

    protected function handlePreviousQuestion(object $message): void
    {
        $menu = Menu::handlePreviousQuestion($message->c, $message->s, $message->q);

        if ($menu === null) {

            $this->answerCallbackQuery([
                'text' => Category::find($message->c)->title,
            ]);

            $this->editMessageText($this->createEditMessage($this->message_id, "Bo'limlar:", Menu::subcategory($message->c)['keyboard']));

            return;
        }

        if (array_key_exists('answerCallbackText', $menu)) {

            $this->answerCallbackQuery([
                'text' => $menu['answerCallbackText'],
            ]);
        }



        $this->editMessageText(

            $this->createEditMessage($this->message_id, $menu['text'], $menu['reply_markup'], $menu['parse_mode'])
        );

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

        $this->editMessageText(
            $this->createEditMessage($this->message_id, $menu['text'], $menu['reply_markup'], $menu['parse_mode'])
        );
    }

    protected function handleWrongAnswer(): void
    {
        $this->telegram::answerCallbackQuery([
            'callback_query_id' => $this->callback_query->getId(),
            'text' => "Noto'g'ri ❌",
            'show_alert' => true,
        ]);
    }
}
