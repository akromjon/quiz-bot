<?php

namespace App\Http\Controllers;

use App\Telegram\Menu\Menu;
use App\Telegram\Message\Message;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Objects\Update;

class TelegramBotController extends Controller
{
    protected Telegram $telegram;
    protected Update $update;

    protected string $chat_id;

    protected string $message;
    public function __construct(Telegram $telegram)
    {
        $this->telegram = $telegram;
    }
    public function handleWebhook(Request $request)
    {
        $telegram = $this->telegram;

        // Handle incoming commands
        $telegram::commandsHandler(true);

        // Get the update from Telegram
        $update = $telegram::getWebhookUpdate();

        $type = $this->validateType($update);


        // Check if the update has a message
        if ('message' === $type) {

            $this->message();
        }

        if ('callback_query' === $type) {

            if ('go_back' === $this->message) {

                $this->baseMenu();
            } else {
                $this->fifth_class();
            }
        }


        return $this->respondSuccess();
    }

    protected function validateType(Update $update): string
    {
        if ($update->isType('message')) {

            $this->message = $update->getMessage()->getText();

            $this->chat_id = $update->getMessage()->getChat()->getId();

            return 'message';
        } else if ($update->isType('callback_query')) {

            $this->message = $update->getCallbackQuery()->getData();

            $this->chat_id = $update->getCallbackQuery()->getMessage()->getChat()->getId();

            return 'callback_query';
        }

        return 'uknown';
    }

    protected function respondSuccess()
    {
        return response()->json(['status' => 'ok'], 200);
    }

    protected function validateMessage(string $m): bool
    {
        $values = array_values(Menu::menus());
        return in_array($m, $values);
    }

    protected function message(): void
    {
        if (!$this->validateMessage($this->message)) {

            $this->telegram::sendMessage([
                'chat_id' => $this->chat_id,
                'text' => 'Notog\'ri Kalit Bosildi!' . $this->message,
            ]);

            return;
        }

        if (Menu::get('theme_based_tests') === $this->message) {

            $reply_markup = Keyboard::make()
                ->inline()
                ->setResizeKeyboard(true)
                ->setOneTimeKeyboard(true)
                ->row([Keyboard::inlineButton(['text' => '5 - Sinf 📖', 'callback_data' => '5_sinf'])])
                ->row([Keyboard::inlineButton(['text' => '6 - Sinf 📖', 'callback_data' => '6_sinf'])])
                ->row([Keyboard::inlineButton(['text' => '7 - Sinf 📖', 'callback_data' => '7_sinf'])])
                ->row([Keyboard::inlineButton(['text' => '8 - Sinf 📖', 'callback_data' => '8_sinf'])])
                ->row([Keyboard::inlineButton(['text' => '9 - Sinf 📖', 'callback_data' => '9_sinf'])])
                ->row([Keyboard::inlineButton(['text' => '10 - Sinf 📖', 'callback_data' => '10_sinf'])])
                ->row([Keyboard::inlineButton(['text' => '11 - Sinf 📖', 'callback_data' => '11_sinf'])])
                ->row([Keyboard::inlineButton(['text' => 'Umumiy Testlar 🚀', 'callback_data' => 'class_general_tests'])])
                ->row([Keyboard::inlineButton(['text' => '⬅️ Orqaga', 'callback_data' => 'go_back'])]);;

            $this->telegram::sendMessage([
                'chat_id' => $this->chat_id,
                'text' => 'Quyudagi Bo\'limlardan Tanlang!',
                'reply_markup' => $reply_markup,
            ]);

            return;
        }
    }



    protected function theme_based_tests(): void {}

    protected function baseMenu()
    {
        $reply_markup = Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row([
                Keyboard::button(Menu::get('free_tests')),
                Keyboard::button(Menu::get('general_tests')),
                Keyboard::button(Menu::get('theme_based_tests'))
            ])
            ->row([
                Keyboard::button(Menu::get('admin')),
                Keyboard::button(Menu::get('help'))
            ])
            ->row([
                Keyboard::button(Menu::get('tariffs'))
            ]);

        $this->telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => "Asosiy Menu",
            'reply_markup' => $reply_markup,
        ]);
    }

    protected function fifth_class()
    {
        $reply_markup = Keyboard::make()
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->row([Keyboard::inlineButton(['text' => '1 - bob . Biologiya tirik organizmlar haqidagi fan 📖            ', 'callback_data' => '5_sinf'])])
            ->row([Keyboard::inlineButton(['text' => '2 - bob . Tirik organizmlarning xilma-xilligi                    ', 'callback_data' => '5_sinf'])])
            ->row([Keyboard::inlineButton(['text' => '3 - bob . Organizam va tashqi muhit                              ', 'callback_data' => '5_sinf'])])
            ->row([Keyboard::inlineButton(['text' => '⬅️ Orqaga                                                      ', 'callback_data' => 'go_back'])]);

        $this->telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => "5 - SINF: ",
            'reply_markup' => $reply_markup,
        ]);

    }
}
