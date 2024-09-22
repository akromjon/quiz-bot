<?php

namespace App\Models\Enums;

enum TelegramUserNotificationEnum: string
{
    case PAID = 'paid';
    case FREE = 'free';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case ALL = 'all';

    public static function getValues(): array
    {
        return [
            self::PAID,
            self::FREE,
            self::ACTIVE,
            self::INACTIVE,
            self::ALL,
        ];
    }
}
