<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

class TelegramUser extends BaseModel
{
    use HasFactory;

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => Carbon::create($value)->format('d.m.Y H:i:s')
        );
    }

    protected function lastPaymentDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Carbon::create($value)->format('d.m.Y H:i:s') : '-'
        );
    }

    protected function nextPaymentDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Carbon::create($value)->format('d.m.Y H:i:s') : '-'
        );
    }

    public static function syncUser(Collection $chat): self
    {
        $user = self::updateOrCreate(
            ['user_id' => $chat->id],
            [
                'username' => $chat->username,
                'first_name' => $chat->first_name,
                'last_name' => $chat->last_name,
            ]
        );

        $user->update([
            'status' => 'active'
        ]);

        return $user;
    }




}
