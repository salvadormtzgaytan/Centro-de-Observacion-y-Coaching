<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DivisionResource\Pages;
use App\Filament\Resources\DivisionResource\RelationManagers;
use App\Models\Division;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DivisionResource extends Resource
{
    // — Menú en español dentro de "Catálogos" —
    protected static ?string $navigationGroup       = 'Catálogos';
    protected static ?int    $navigationSort        = 3;
    protected static ?string $navigationLabel       = 'Divisiones';
    protected static ?string $modelLabel            = 'División';
    protected static ?string $pluralModelLabel      = 'Divisiones';

    protected static ?string $model          = Division::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->label(__('division.fields.key'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->label(__('division.fields.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('order')
                    ->label(__('division.fields.order'))
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
                    ->label(__('division.fields.key'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('division.fields.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('order')
                    ->label(__('division.fields.order'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('division.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('division.fields.updated_at'))
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
            'index' => Pages\ListDivisions::route('/'),
            'create' => Pages\CreateDivision::route('/create'),
            'edit' => Pages\EditDivision::route('/{record}/edit'),
        ];
    }
}
