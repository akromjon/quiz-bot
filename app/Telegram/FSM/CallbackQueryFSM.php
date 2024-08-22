<?php

namespace App\Telegram\FSM;

use App\Telegram\Menu\Menu;
use Telegram\Bot\Keyboard\Keyboard;

class CallbackQueryFSM extends Base
{
    public  function route(): void
    {
        $message = json_decode($this->message);


        match ($message->m) {

            'base' => $this->sendMessage([
                'text' => 'Asosiy Menu:',
                'reply_markup' => Menu::base()
            ]),

            // C = Category::class
            'C' => $this->sendMessage([
                'text' => 'Sinflar: ',
                'reply_markup' => Menu::category()
            ]),

            // S = SubCategory::class
            'S' => $this->sendMessage([
                'text' => "Bo'limlar:",
                'reply_markup' => Menu::subcategory($message->id)
            ]),

            // Q = Question::class
            'Q' => $this->handleQuestion($message),

            // W = Wrong Answer
            'W' => $this->handleWrongAnswer(),

            default => $this->sendMessage(['text' => 'Hozirda Bu boyicha ishlamoqdamiz...!']),
        };
    }

    protected function handleQuestion(object $message)
    {
        if (property_exists($message, 'q')) {

            $menu = Menu::question($message->c, $message->s, $message->q, true);
        } else {

            $menu = Menu::question($message->c, $message->s);
        }

        match ($menu['type']) {

            'message' => $this->sendMessage([
                'text' => $menu['text'],
                'reply_markup' => $menu['reply_markup'],
                'parse_mode' => $menu['parse_mode']
            ]),

            'edit_message' => $this->editMessageText([
                'message_id' => $this->callback_query->getMessage()->message_id,
                'text' => $menu['text'],
                'reply_markup' => $menu['reply_markup'],
                'parse_mode' => $menu['parse_mode']
            ]),

            default => null,
        };
    }
    protected function handleWrongAnswer()
    {
        $this->telegram::answerCallbackQuery([
            'callback_query_id' => $this->callback_query->getId(),
            'text' => "Noto'g'ri ❌",
            'show_alert' => true,
        ]);
    }

    public static function handle(...$params): self
    {
        $ins = new self(...$params);

        $ins->route();

        return $ins;
    }
}
