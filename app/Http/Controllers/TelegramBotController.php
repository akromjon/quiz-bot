<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Level;
use App\Models\Question;
use App\Models\SubCategory;
use App\Telegram\Menu\Menu;
use Illuminate\Http\Request;
use Telegram\Bot\Keyboard\Keyboard;

class TelegramBotController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $telegram = $this->telegram;

        $update = $this->init($telegram);

        $type = $this->objectType($update);

        if ("StartCommand" === $this->message || "/start" === $this->message) {
            return;
        }

        match ($type) {
            'message' => $this->route_message(),
            'callback_query' => $this->route_callback_query(),
        };

        return $this->respondSuccess();
    }

    protected function route_message()
    {

        match ($this->message) {

            'Sinflar' => $this->sendMessage([
                'text' => 'Sinflar:',
                'reply_markup' => Menu::category()
            ]),

            default => $this->sendMessage(['text' => 'Hozirda Bu boyicha ishlamoqdamiz...!']),
        };
    }

    protected function route_callback_query()
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

            $menu = Menu::question($message->s, $message->s);
        }



        match ($menu['type']) {

            'message' => $this->sendMessage([
                'text' => $menu['text'],
                'reply_markup' => $menu['reply_markup'],
                'parse_mode' => $menu['parse_mode']
            ]),

            default => null,
        };
    }

    protected function handleWrongAnswer()
    {
        $keyboard = Keyboard::make()
            ->inline()
            ->row(
                [Keyboard::inlineButton(['text' => 'Updated Button', 'callback_data' => 'new_data'])]
            );


        $this->telegram::editMessageText([
            'chat_id' => $this->chat_id,
            'message_id' => $this->callbackQuery->getMessage()->message_id,
            'text' => 'You selected an option!',
            'reply_markup' => $keyboard,
            'parse_mode' => 'HTML',
        ]);

        // $this->telegram::answerCallbackQuery([
        //     'callback_query_id' => $this->callbackQuery->getId(),
        //     'text' => "Noto'g'ri ❌",
        //     'show_alert' => true,
        // ]);
    }
}
