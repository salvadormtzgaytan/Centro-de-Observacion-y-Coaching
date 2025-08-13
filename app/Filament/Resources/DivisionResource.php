<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DivisionResource\Pages;
use App\Models\Division;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DivisionResource extends Resource
{
    protected static ?string $model = Division::class;

    // — Menú en español dentro de "Catálogos" —
    protected static ?string $navigationGroup = 'Catálogos';

    protected static ?int $navigationSort = 60;

    protected static ?string $navigationLabel = 'Líneas Terapéuticas';

    protected static ?string $modelLabel = 'Línea Terapéutica';

    protected static ?string $pluralModelLabel = 'Líneas Terapéuticas';

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->label(__('filament.resources.division.fields.key'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('name')
                    ->label(__('filament.resources.division.fields.name'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('order')
                    ->label(__('filament.resources.division.fields.order'))
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
                    ->label(__('filament.resources.division.fields.key'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.resources.division.fields.name'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('order')
                    ->label(__('filament.resources.division.fields.order'))
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDivisions::route('/'),
            'create' => Pages\CreateDivision::route('/create'),
            'edit' => Pages\EditDivision::route('/{record}/edit'),
        ];
    }
}
