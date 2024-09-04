<?php
namespace App\Filament\Pages\Settings;

use Closure;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Outerweb\FilamentSettings\Filament\Pages\Settings as BaseSettings;

class Settings extends BaseSettings
{
    public function schema(): array|Closure
    {
        return [
            Tabs::make('Settings')
                ->schema([
                    Tabs\Tab::make('Bot Settings')
                        ->schema([
                            TextInput::make('admin_username')
                                ->label('Admin Username')
                                ->placeholder('Admin Username')
                                ->required(),

                            TextInput::make('admin_username_link')
                                ->url()
                                ->label('Admin Username Link')
                                ->placeholder('Admin Username Link')
                                ->required(),
                            Textarea::make('welcome_message')
                                ->required()
                                ->autosize()
                                ->label('Welcome Message'),
                            Textarea::make('how_bot_works')
                                ->required()
                                ->autosize()
                                ->label('How Bot Works?'),

                        ]),

                ]),
        ];
    }
}