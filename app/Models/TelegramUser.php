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

    public static function createOrUpdate(Collection $chat,bool $allow_to_update=false): self
    {

        $user = self::where('user_id', $chat->id)->first();

        if (null===$user) {

            return self::create([
                'user_id' => $chat->id,
                'username' => $chat->username,
                'first_name' => $chat->first_name,
                'last_name' => $chat->last_name,
                'status' => 'blocked'
            ]);

        }

        if (false===$allow_to_update) {
            
            return $user;

        }

        $user->update([
            'username' => $chat->username,
            'first_name' => $chat->first_name,
            'last_name' => $chat->last_name,
        ]);

        return $user;
    }




}
