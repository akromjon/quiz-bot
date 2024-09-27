<?php

namespace App\Telegram\FSM;

use Illuminate\Support\Facades\Log;
use App\Telegram\Menu\Menu;
use App\Telegram\Middleware\CheckUserIsPaidOrNotMiddleware;

class MessageFSM extends Base
{
    public function route(): void
    {
        $lets_check = CheckUserIsPaidOrNotMiddleware::handle($this->message);

        if (!$lets_check) {

            $this->sendMessage(Menu::handleUnpaidService());

            return;
        }

        match ($this->message) {
            '📚 Mavzulashtirilgan Testlar' => $this->sendMessage(Menu::category()),
            '🧩 Mix Testlar' => $this->sendMessageOrFile(Menu::handeMixQuiz()),
            '🆓 Bepul Testlar' => $this->sendMessageOrFile(Menu::handleFreeQuiz()),
            '👤 Mening Profilim'=>$this->sendMessage(Menu::profile($this->chat_id)),
            '🤔 Bot qanday ishlaydi?'=>$this->sendMessage(Menu::howBotWorks()),
            // 'invoice' => $this->handleInvoice(),
            default => Log::error('Unknown message type returned from MessageFSM'),
        };
    }

    protected function handleInvoice()
    {
        $this->sendInvoice([

            "title" => "Tarif", // Product title
            "description" => "Pullik Tarif sotib olish", // Product description
            "payload" => json_encode([
                'user_id' => $this->chat_id,
                'amount' => 100,
                'product_id' => 1,
                'product_name' => 'Test Product',
            ]), // Product payload, not required for now
            "currency" => "XTR", // Stars Currency
            "prices" => [ // Price list
                [
                    "label" => "Test Product", // Price label
                    "amount" => 45, // Price amount
                ]
            ]
        ]);
    }


}
