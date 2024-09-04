<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;


class SubCategory extends BaseModel
{
    use HasFactory;

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->orderBy('id');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active',true);
    } 
}
