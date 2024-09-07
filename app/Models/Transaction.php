<?php

namespace App\Models;

use App\Models\Enums\TransactionStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => TransactionStatusEnum::class,
        ];
    }

    public function telegramUser()
    {
        return $this->belongsTo(TelegramUser::class);
    }
}
