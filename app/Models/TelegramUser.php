<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TelegramUser extends BaseModel
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

   
}
