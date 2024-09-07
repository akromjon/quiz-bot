<?php

namespace App\Models;

use App\Models\Enums\TransactionStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;

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

    public static function createOrUpdate(Collection $chat, bool $allow_to_update = false): self
    {

        $user = self::where('user_id', $chat->id)->first();

        if (null === $user) {

            return self::create([
                'user_id' => $chat->id,
                'username' => $chat->username,
                'first_name' => $chat->first_name,
                'last_name' => $chat->last_name,
                'status' => 'blocked'
            ]);

        }

        if (false === $allow_to_update) {

            return $user;

        }

        $user->update([
            'username' => $chat->username,
            'first_name' => $chat->first_name,
            'last_name' => $chat->last_name,
        ]);

        return $user;
    }


    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public static function setCurrentUser(TelegramUser $user): void
    {
        Config::set('current_user', $user);
    }

    public static function getCurrentUser(): ?TelegramUser
    {
        return Config::get('current_user');
    }

    public static function setLastMessage(string $message): void
    {
        $user = self::getCurrentUser();

        Cache::rememberForever("user_{$user->user_id}", function () use ($message) {
            return $message;
        });
    }

    public static function getLastMessage(): ?string
    {
        $user = self::getCurrentUser();

        return Cache::get("user_{$user->user_id}");
    }

    public static function clearLastMessage(): bool
    {
        $user = self::getCurrentUser();

        return Cache::forget("user_{$user->user_id}");
    }

    public static function checkCurrentTransactionStatus(): bool
    {
        $user = self::getCurrentUser();

        return $user->transactions()->where('status', TransactionStatusEnum::PENDING)->exists();
    }




}
