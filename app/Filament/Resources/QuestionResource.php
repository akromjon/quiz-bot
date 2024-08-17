<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Filament\Resources\QuestionResource\RelationManagers;
use App\Models\Question;
use App\Models\SubCategory;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
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
                        Select::make('sub_category_id')->relationship('subCategory', 'title')->label('Sub Categoty'),
                        FileUpload::make('image')->label('Image'),
                        TextInput::make('question')->label("Question")->required(),
                    ])
                    ->live()
                    ->columns(1),

                Fieldset::make('Add Options')
                    ->schema([
                        Repeater::make('questionOptions')
                            ->relationship('questionOptions')
                            ->label('Options')
                            ->schema([
                                TextInput::make('option')
                                    ->label('Option')
                                    ->required(),
                                Toggle::make('is_answer')->required(),
                            ])
                            ->columns(1),
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
                TextColumn::make('id')->sortable()->searchable(),
                TextColumn::make("subCategory")->label('Sub Category')
                    ->formatStateUsing(function ($state, Question $c) {

                        $title = $c->subCategory?->category?->title . ', ' . $c->subCategory->title;

                        return $title;
                    }),
                ImageColumn::make('image')->circular(),
                TextColumn::make('question')->sortable()->searchable(),
                ToggleColumn::make('is_active')->sortable()->searchable(),
                ToggleColumn::make('is_free')->sortable()->searchable(),

            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
