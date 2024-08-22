<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TelegramUser extends Model
{
    use HasFactory;

    public static function syncUser(Collection $chat): self
    {
        return self::updateOrCreate(
            ['user_id' => $chat->id],
            [
                'username' => $chat->username,
                'first_name' => $chat->first_name,
                'last_name' => $chat->last_name,
            ]
        );
    }

    public static function cacheLastMessageId(int $user_id, int $message_id): void
    {
        Cache::rememberForever("$user_id:last_message_id", function () use ($message_id) {
            return $message_id;
        });
    }

    public static function getLastMessageId(int $user_id): int
    {
        return Cache::get("$user_id:last_message_id");
    }

    public static function forgetLastMessage(int $user_id, int $last_message_id): void
    {
        Cache::forget("$user_id:last_message_id");
    }
}
