<?php

namespace App\Filament\Resources\TelegramUserNotificationResource\Pages;

use App\Filament\Resources\TelegramUserNotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTelegramUserNotifications extends ListRecords
{
    protected static string $resource = TelegramUserNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
