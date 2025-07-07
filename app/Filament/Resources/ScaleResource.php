<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScaleResource\Pages;
use App\Filament\Resources\ScaleResource\RelationManagers;
use App\Models\Scale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ScaleResource extends Resource
{
    protected static ?string $navigationGroup       = 'Catálogos';
    protected static ?int    $navigationSort        = 2;
    protected static ?string $navigationLabel       = 'Escalas';
    protected static ?string $modelLabel            = 'Escala';
    protected static ?string $pluralModelLabel      = 'Escalas';

    protected static ?string $model                  = Scale::class;
    protected static ?string $navigationIcon         = 'heroicon-o-adjustments-horizontal';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('label')
                    ->label(__('scale.fields.label'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('value')
                    ->label(__('scale.fields.value'))
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('order')
                    ->label(__('scale.fields.order'))
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label(__('scale.fields.label'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('value')
                    ->label(__('scale.fields.value'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order')
                    ->label(__('scale.fields.order'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('scale.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('scale.fields.updated_at'))
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
            'index' => Pages\ListScales::route('/'),
            'create' => Pages\CreateScale::route('/create'),
            'edit' => Pages\EditScale::route('/{record}/edit'),
        ];
    }
}
