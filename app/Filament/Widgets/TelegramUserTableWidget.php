<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\TelegramUserResource;
use App\Models\Enums\TelegramUserStatusEnum;
use App\Models\Enums\TelegramUserTariffEnum;
use Filament\Tables;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TelegramUserTableWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '15s';

    public function table(Table $table): Table
    {

        return $table
            ->query(
                TelegramUserResource::getEloquentQuery()
            )
            ->defaultSort('last_used_at', 'desc')
            ->columns([
                TextColumn::make('id')->sortable()->searchable(),
                TextColumn::make('user_id')->sortable()->numeric()->copyable(),
                TextColumn::make('username')->formatStateUsing(function ($record) {
                    return '@' . $record->username ?? '';
                })->sortable()->copyable()->copyableState(function ($record) {
                    return '@' . $record->username;
                })->searchable(),
                TextColumn::make('first_name')->sortable()->searchable(),
                SelectColumn::make('status')->options(TelegramUserStatusEnum::class)->inline()->sortable()->searchable(),
                SelectColumn::make('tariff')->options(TelegramUserTariffEnum::class)->sortable()->searchable(),
                TextColumn::make('created_at')->dateTime()->sortable()->searchable(),
                TextColumn::make('last_used_at')->dateTime()->sortable()->searchable(),
            ])
            ->actions(
                TelegramUserResource::getCustomAction()
            );

    }
}
