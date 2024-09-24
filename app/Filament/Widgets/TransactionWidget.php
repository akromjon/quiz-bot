<?php

namespace App\Filament\Widgets;

use App\Models\Enums\TransactionStatusEnum;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TransactionWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $total_transactions = Transaction::where('status', TransactionStatusEnum::APPROVED)->sum('amount');

        $weekly_transactions = Transaction::where('status', TransactionStatusEnum::APPROVED)
            ->where('created_at', '>=', now()->subWeek())
            ->sum('amount');

        $monthly_transactions = Transaction::where('status', TransactionStatusEnum::APPROVED)
            ->where('created_at', '>=', now()->subMonth())
            ->sum('amount');

        $daily_transactions = Transaction::where('status', TransactionStatusEnum::APPROVED)
            ->where('created_at', '>=', now()->subDay())
            ->sum('amount');

        return [
            Stat::make('Total Transactions', number_format($total_transactions, 0) . " UZS")
                ->color('success'),
            Stat::make('Monthly Transactions', number_format($monthly_transactions, 0) . " UZS")
                ->color('warning'),
            Stat::make('Weekly Transactions', number_format($weekly_transactions, 0) . " UZS")
                ->color('info'),
            Stat::make('Daily Transactions', number_format($daily_transactions, 0) . " UZS")
                ->color('danger'),

        ];
    }
}
