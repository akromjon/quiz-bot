<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    public static function boot(): void
    {
        parent::boot();

        static::saved(function () {
            cache()->forget('categories');
            cache()->forget('sub_categories');
            cache()->forget('questions');
            cache()->forget('question_options');
            cache()->forget('telegram_users');
            cache()->forget('users');
        });
    }

}
