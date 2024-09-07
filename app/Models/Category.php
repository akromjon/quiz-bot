<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class Category extends BaseModel
{
    use HasFactory;

    public function getTrimmedTitleAttribute(): string
    {
        return trim(str_replace('📖', '', $this->title));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public static function getCachedCategories(): Collection
    {
        return cache()->rememberForever('categories', function () {
            return self::active()->get();
        });
    }
}
