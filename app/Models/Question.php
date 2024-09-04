<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;


class Question extends BaseModel
{
    use HasFactory;

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function questionOptions(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active',true);
    }
}
