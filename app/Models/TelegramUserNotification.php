<?php

namespace App\Models;

use App\Models\Enums\TelegramUserNotificationEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class TelegramUserNotification extends Model
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'params' => 'array',
            'send_to' => TelegramUserNotificationEnum::class,
        ];
    }
}
