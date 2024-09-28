<?php
namespace App\Filament\Pages\Settings;

use Closure;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;
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
                            TextInput::make('trial_day')->label('Trial day')->placeholder('Trial day')->required(),
                            TextInput::make('tariff_amount')->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')
                                ->numeric()
                                ->label('Tariff Amount')
                                ->required(),
                            TextInput::make('admin_username')->label('Admin Username')->placeholder('Admin Username')->required(),
                            TextInput::make('admin_username_link')->url()->label('Admin Username Link')->placeholder('Admin Username Link')->required(),
                            Textarea::make('welcome_message')->required()->autosize()->label('Welcome Message'),
                            Textarea::make('admin_message')->required()->autosize()->label('Admin Message'),
                            Textarea::make('how_bot_works')->required()->autosize()->label('How Bot Works?'),
                            Textarea::make('free_quiz_finished_message')->required()->autosize()->label('Free Quiz Finished Message'),
                            Textarea::make('unpaid_service_message')->required()->autosize()->label('Unpaid Service Message'),

                        ]),

                    Tabs\Tab::make('Transaction Settings')
                        ->schema([
                            Textarea::make('receipt_approved_message')
                                ->required()
                                ->autosize()
                                ->label('Receipt Approved Message'),
                            Textarea::make('receipt_rejected_message')
                                ->required()
                                ->autosize()
                                ->label('Receipt Rejected Message'),

                        ]),
                    Tabs\Tab::make('Terms and Conditions & Privacy Policy')
                        ->schema([
                            Textarea::make('terms_and_conditions')
                                ->required()
                                ->autosize()
                                ->label('Terms and Conditions'),
                            Textarea::make('privacy_policy')
                                ->required()
                                ->autosize()
                                ->label('Privacy Policy'),


                        ]),
                    Tabs\Tab::make('Free Question')
                        ->schema([
                            TextInput::make('free_question_limit')->numeric()->label('Free Question Limit')->required()->default(30),
                        ]),

                ]),
        ];
    }
}
