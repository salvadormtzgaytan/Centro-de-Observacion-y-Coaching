<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LevelResource\Pages;
use App\Filament\Resources\LevelResource\RelationManagers;
use App\Models\Level;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LevelResource extends Resource
{
    // — Menú en español dentro de "Catálogos" —
    protected static ?string $navigationGroup       = 'Catálogos';
    protected static ?int    $navigationSort        = 4;
    protected static ?string $navigationLabel       = 'Niveles';
    protected static ?string $modelLabel            = 'Nivel';
    protected static ?string $pluralModelLabel      = 'Niveles';

    protected static ?string $model          = Level::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->label(__('level.fields.key'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->label(__('level.fields.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('order')
                    ->label(__('level.fields.order'))
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label(__('level.fields.key'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('level.fields.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('order')
                    ->label(__('level.fields.order'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('level.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('level.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListLevels::route('/'),
            'create' => Pages\CreateLevel::route('/create'),
            'edit' => Pages\EditLevel::route('/{record}/edit'),
        ];
    }
}
