<?php

namespace App\Telegram\FSM;

use App\Models\TelegramUser;
use App\Telegram\FSM\Base;
use App\Telegram\Menu\Menu;


class CommandFSM extends Base
{
    protected function route(): void
    {
        match ($this->message) {
            '/start' => $this->handleStart(),
            '/profile' => $this->sendMessage(Menu::profile($this->chat_id)),
            '/chekyuborish' => $this->handleReceipt(),
            '/help' => $this->sendMessage(Menu::howBotWorks()),
            '/admin' => $this->sendMessage(Menu::admin()),
            '/terms'=> $this->sendMessage(Menu::termsAndConditions()),
            '/privacy'=> $this->sendMessage(Menu::privacyPolicy()),
            default => null
        };
    }

    protected function handleStart()
    {
        $user = TelegramUser::createOrUpdate($this->update->getChat(), true);

        $this->sendMessage(Menu::base($user));
    }

    protected function handleReceipt()
    {
        if (TelegramUser::checkCurrentTransactionStatus()) {

            $this->sendMessage(Menu::receiptAlreadyExists());

            return;
        }

        TelegramUser::setLastMessage('chekyuborish');

        $this->sendMessage(Menu::receipt());
    }

}
