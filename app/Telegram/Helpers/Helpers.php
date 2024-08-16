<?php


namespace App\Telegram\Helpers;

use Telegram\Bot\Actions;

trait Helpers
{
    public function typing(): void
    {
        $this->replyWithChatAction(['action' => Actions::TYPING]);
    }
}
