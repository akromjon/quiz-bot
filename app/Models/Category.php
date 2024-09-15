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

    public function subCategories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SubCategory::class)->orderBy('id');
    }

    public static function boot(): void
    {
        parent::boot();

        static::saved(function ($model) {

            if ($model->isDirty('excel_file_path') || $model->isDirty('start_sheet_number') || $model->isDirty('end_sheet_number')) {

                foreach (range($model->start_sheet_number, $model->end_sheet_number) as $sheet_number) {

                    $model->subCategories()->create([
                        'title' => "{$sheet_number}-BOB",
                        'excel_file_path' => $model->excel_file_path,
                        'sheet_number' => $sheet_number,
                        'is_active' => true,
                    ]);
                }
            }

        });
    }
}
