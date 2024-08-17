<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Level;
use App\Models\SubCategory;
use App\Telegram\Menu\Menu;
use Illuminate\Http\Request;

class TelegramBotController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $telegram = $this->telegram;

        $update = $this->init($telegram);

        $type = $this->objectType($update);

        if ("StartCommand" === $this->message || "/start"===$this->message) {
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

        match ($message->model) {

            'base' => $this->sendMessage([
                'text' => 'Asosiy Menu:',
                'reply_markup' => Menu::base()
            ]),

            Category::class => $this->sendMessage([
                'text' => 'Sinflar: ',
                'reply_markup' => Menu::category()
            ]),

            SubCategory::class => $this->sendMessage([
                'text' => "Bo'limlar:",
                'reply_markup' => Menu::subcategory($message->id)
            ]),          


            default => $this->sendMessage(['text' => 'Hozirda Bu boyicha ishlamoqdamiz...!']),
        };
    }
}
