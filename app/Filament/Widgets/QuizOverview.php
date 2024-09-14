<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Question;
use App\Models\SubCategory;
use App\Models\TelegramUser;

class QuizOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';
    protected function getStats(): array
    {
        $categories = Category::count();

        $questions = Question::count();

        $subcategories = SubCategory::count();

        return [
            Stat::make('Total Categories', $categories)
                ->color('success'),
            Stat::make('Total Subcategories', $subcategories)
                ->color('success'),
            Stat::make('Total Questions', $questions)
                ->color('success'),


        ];
    }
}
