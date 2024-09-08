<?php

namespace App\Filament\Resources\TelegramUserResource\RelationManagers;

use App\Models\Enums\TransactionStatusEnum;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('amount')->mask(RawJs::make('$money($input)'))
                ->stripCharacters(',')
                ->numeric()
                ->required(),
                Textarea::make('comment')->nullable(),
                DateTimePicker::make('payment_date')->required()->date(),
                Select::make('status')->options(TransactionStatusEnum::class),
                FileUpload::make('receipt_path')->downloadable()->previewable()->image(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
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
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
