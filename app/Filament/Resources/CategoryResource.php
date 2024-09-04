<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Symfony\Component\Console\Input\Input;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Actions\SelectAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    // protected static ?string $navigationIcon = 'heroicon-c-square-3-stack-3d';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = "Category";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Add a Category')
                    ->schema([
                        TextInput::make('title')->required(),
                        Toggle::make('is_active')->default(false),
                    ])
                    ->live()
                    ->columns(1),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->searchable(),
                TextColumn::make('title')->inline()->searchable()->sortable(),
                ToggleColumn::make('is_active')->searchable()->sortable(),
            ])
            ->filters([
                // Filter by title
               
                
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
            ])->defaultSort('id', 'desc');
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
