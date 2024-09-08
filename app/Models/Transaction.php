<?php

namespace App\Models;

use App\Models\Enums\TransactionStatusEnum;
use App\Notifications\TransactionApproved;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
class Transaction extends BaseModel
{
    use HasFactory;

    use Notifiable;

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


    public static function boot(): void
    {
        parent::boot();

        static::saved(function ($self) {

            if ($self->isDirty('status') && $self->status === TransactionStatusEnum::APPROVED && $self->getOriginal('status') === TransactionStatusEnum::PENDING) {
                $self->notify(new TransactionApproved($self));
            }

        });
    }


}
