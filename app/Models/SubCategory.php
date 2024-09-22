<?php

namespace App\Models;

use App\Imports\ImportExcel;
use App\Imports\QuestionImport;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SubCategory extends BaseModel
{
    use HasFactory;

    public function questions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Question::class)->where('is_active', true)->orderBy('id');
    }

    public function questionCount(): int
    {
        return Cache::rememberForever("question_count_{$this->id}", function () {
            return $this->questions()->count();
        });
    }

    public static function boot(): void
    {
        parent::boot();

        static::saved(function ($model) {

            if(($model->isDirty('excel_file_path') || $model->isDirty('sheet_number')) && $model->excel_file_path!==null){

                static::ImportExcel($model);
            }

        });
    }

    public static function ImportExcel(SubCategory $model)
    {
        ImportExcel::run(new QuestionImport($model->id, $model->sheet_number), Storage::path("public/{$model->excel_file_path}"));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }




}
