<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Filament\Resources\QuestionResource\RelationManagers;
use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationGroup = "Quiz";


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Add a question')
                    ->schema([
                        Select::make('level_id')->relationship('level', 'title')->label('Level'),
                        FileUpload::make('image')->label('Image'),
                        TextInput::make('question')->label("Question")->required(),
                    ])
                    ->live()
                    ->columns(1),

                Fieldset::make('Add Options')
                    ->schema([
                        Repeater::make('Options')
                            ->schema([
                                TextInput::make('a')->required(),
                                TextInput::make('b')->required(),
                                TextInput::make('c'),
                                TextInput::make('d'),
                            ])
                            ->columns(2)
                    ])
                    ->live()
                    ->columns(1),
                Toggle::make('is_active')->label('Is Active')->default(true),
                Toggle::make('is_free')->label('Is Free')->default(false)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
