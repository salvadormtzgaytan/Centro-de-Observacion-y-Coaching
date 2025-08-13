<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScaleResource\Pages;
use App\Models\Scale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ScaleResource extends Resource
{
    protected static ?string $model = Scale::class;

    // Menú en español dentro de "Catálogos"
    protected static ?string $navigationGroup = 'Catálogos';

    protected static ?int $navigationSort = 60;

    protected static ?string $navigationLabel = 'Escalas';

    protected static ?string $modelLabel = 'Escala';

    protected static ?string $pluralModelLabel = 'Escalas';

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('label')
                    ->label(__('filament.resources.scale.fields.label'))
                    ->required()
                    ->maxLength(255)
                    ->placeholder(__('filament.resources.scale.placeholders.label')),

                Forms\Components\TextInput::make('value')
                    ->label(__('filament.resources.scale.fields.value'))
                    ->required()
                    ->numeric()
                    ->step(0.01)
                    ->placeholder(__('filament.resources.scale.placeholders.value')),

                Forms\Components\TextInput::make('order')
                    ->label(__('filament.resources.scale.fields.order'))
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->placeholder(__('filament.resources.scale.placeholders.order')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label(__('filament.resources.scale.fields.label'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('value')
                    ->label(__('filament.resources.scale.fields.value'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order')
                    ->label(__('filament.resources.scale.fields.order'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.resources.scale.fields.created_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('filament.resources.scale.fields.updated_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('recent')
                    ->label(__('filament.resources.scale.filters.recent'))
                    ->query(fn ($query) => $query->where('created_at', '>=', now()->subMonth())),
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
            'index' => Pages\ListScales::route('/'),
            'create' => Pages\CreateScale::route('/create'),
            'edit' => Pages\EditScale::route('/{record}/edit'),
        ];
    }
}
