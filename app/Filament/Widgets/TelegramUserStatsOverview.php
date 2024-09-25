<?php

namespace App\Filament\Widgets;

use App\Models\Enums\TelegramUserStatusEnum;
use App\Models\Enums\TelegramUserTariffEnum;
use App\Models\TelegramUser;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TelegramUserStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';
    protected function getStats(): array
    {
        $total_users = TelegramUser::count();

        $paid_users = TelegramUser::where('tariff', TelegramUserTariffEnum::PAID)->count();

        $free_users = TelegramUser::where('tariff', TelegramUserTariffEnum::FREE)->count();

        $blocked_users = TelegramUser::where('status', TelegramUserStatusEnum::BLOCKED)->count();

        return [
            Stat::make('👤 Total Users', $total_users)
                ->color('success'),
            Stat::make('💎 Paid Users', $paid_users)
                ->color('success'),

            Stat::make('🆓 Free Users', $free_users)
                ->color('success'),

            Stat::make('❌ Blocked Users', $blocked_users)
                ->color('success'),
        ];
    }
}
