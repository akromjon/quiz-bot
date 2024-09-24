<?php

namespace App\Models\Enums;

enum TelegramUserTariffEnum: string
{
    case FREE = 'free';
    case PAID = 'paid';

    public static function getValues(): array
    {
        return [
            self::FREE,
            self::PAID,
        ];
    }
}