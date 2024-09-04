<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Category extends BaseModel
{
    use HasFactory;

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public static function getCachedCategories(): Collection
    {
        return cache()->rememberForever('categories', function () {
            return self::active()->get();
        });
    }
}
