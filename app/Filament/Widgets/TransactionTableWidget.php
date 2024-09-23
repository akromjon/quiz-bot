<?php

namespace App\Filament\Widgets;

use App\Models\Enums\TransactionStatusEnum;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Transaction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;

class TransactionTableWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '15s';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
            )
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('amount')->money('UZS'),
                TextColumn::make('receipt_path')->label('Receipt')->formatStateUsing(function($record){
                    return "📄 view";
                })->url(function($record){
                    return "/storage$record->receipt_path";
                })->openUrlInNewTab(true),
                SelectColumn::make('status')->options(TransactionStatusEnum::class),
                TextColumn::make('payment_date'),
                TextColumn::make('created_at'),
                TextColumn::make('updated_at'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
