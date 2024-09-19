<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

abstract class BaseModel extends Model
{
    public static function boot(): void
    {
        parent::boot();

        static::saved(function () {
            // Artisan::call('cache:clear');
        });
    }

}
