<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TelegramUserNotificationResource\Pages;
use App\Filament\Resources\TelegramUserNotificationResource\RelationManagers;
use App\Models\Enums\TelegramUserNotificationEnum;
use App\Models\TelegramUserNotification;
use App\Notifications\TelegramUserNotification as AliasedTelegramUserNotification;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TelegramUserNotificationResource extends Resource
{
    protected static ?string $model = TelegramUserNotification::class;

    protected static ?string $navigationGroup = "Telegram";



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Params')
                    ->statePath('params')
                    ->schema([
                        FileUpload::make('photo')->image(),
                        Textarea::make('text')->autosize(),
                        Repeater::make('inlineButtons')
                            ->schema([
                                TextInput::make('text'),
                                TextInput::make('url'),
                                TextInput::make('callback_data'),
                            ]),

                    ]),
                Select::make('send_to')->options(TelegramUserNotificationEnum::class)->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->searchable(),
                Tables\Columns\ImageColumn::make('params.photo')->circular()->label('Photo')->sortable(),
                Tables\Columns\TextColumn::make('params.text')->limit(20)->label('Text')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('send_to')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->searchable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make(name: 'Send')
                    ->action(function (TelegramUserNotification $record) {
                        self::notificationAction($record);
                    })
                    ->icon('heroicon-m-play')
                    ->requiresConfirmation()
                    ->color('primary'),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function notificationAction(TelegramUserNotification $telegramUserNotification): void
    {
        $telegramUserNotification->notify(new AliasedTelegramUserNotification($telegramUserNotification));
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
            'index' => Pages\ListTelegramUserNotifications::route('/'),
            'create' => Pages\CreateTelegramUserNotification::route('/create'),
            'edit' => Pages\EditTelegramUserNotification::route('/{record}/edit'),
        ];
    }


}
