<?php

namespace App\Models;

use App\Models\Enums\TransactionStatusEnum;
use App\Notifications\TransactionApprovedNotification;
use App\Notifications\TransactionRejectedNotification;
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
                $self->notify(new TransactionApprovedNotification($self));
            }

            if ($self->isDirty('status') && $self->status === TransactionStatusEnum::REJECTED && $self->getOriginal('status') === TransactionStatusEnum::PENDING) {
                $self->notify(new TransactionRejectedNotification($self));
            }

        });
    }


}
