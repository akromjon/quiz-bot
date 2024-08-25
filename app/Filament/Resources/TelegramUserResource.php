<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TelegramUserResource\Pages;
use App\Filament\Resources\TelegramUserResource\RelationManagers;
use App\Models\TelegramUser;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TelegramUserResource extends Resource
{
    protected static ?string $model = TelegramUser::class;

    protected static ?string $navigationGroup = "Telegram";


    // protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make("user_id")->required()->integer(),
                TextInput::make('username')->required()->string(),
                TextInput::make('first_name')->required()->string(),
                TextInput::make('last_name')->required()->string(),
                Select::make('status')->options([
                    'active' => 'active',
                    'inactive' => 'inactive',
                ]),
                Select::make('tariff')->options([
                    'free' => 'free',
                    'paid' => 'paid',
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->searchable(),
                TextColumn::make('user_id')->sortable()->numeric()->copyable(),
                TextColumn::make('username')->sortable()->searchable(),
                TextColumn::make('first_name')->sortable()->searchable(),
                TextColumn::make('last_name')->sortable()->searchable(),
                TextColumn::make('status')->sortable()->searchable(),
                TextColumn::make('tariff')->sortable()->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTelegramUsers::route('/'),
            'create' => Pages\CreateTelegramUser::route('/create'),
            'edit' => Pages\EditTelegramUser::route('/{record}/edit'),
        ];
    }
}
