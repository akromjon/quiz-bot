<?php

namespace App\Telegram\Middleware;

use App\Models\Enums\TelegramUserTariffEnum;

class CheckUserIsPaidOrNotMiddleware extends BaseMiddleware
{

    // 'base' => $this->base(),
    // 'C' => $this->handleCategory(), // Category
    // 'S' => $this->handleSubCategory($this->message), // SubCategory
    // 'Q' => $this->handleQuestion($this->message),  // Question
    // 'W' => $this->answerCallbackQuery(Menu::handleWrongAnswer()),
    // 'M' => $this->handleMixQuiz(), // Mix Quiz
    protected static array $paidServices = [
        'S',
        'Q',
        'W',
        'M',
        '🧩 Mix Testlar'
    ];
    public static function handle(?string $message): bool
    {
        if($message === null) {
            return true;
        }

        if (currentTelegramUser()->tariff === TelegramUserTariffEnum::PAID) {
            return true;
        }

        if (in_array($message, self::$paidServices)) {

            return false;
        }

        return true;
    }
}
