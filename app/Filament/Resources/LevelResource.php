<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LevelResource\Pages;
use App\Models\Level;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LevelResource extends Resource
{
    protected static ?string $model = Level::class;

    // Menú en español dentro de "Catálogos"
    protected static ?string $navigationGroup = 'Catálogos';

    protected static ?int $navigationSort = 60;

    protected static ?string $navigationLabel = 'Niveles';

    protected static ?string $modelLabel = 'Nivel';

    protected static ?string $pluralModelLabel = 'Niveles';

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->label(__('filament.resources.level.fields.key'))
                    ->required()
                    ->maxLength(255)
                    ->placeholder(__('filament.resources.level.placeholders.key')),

                Forms\Components\TextInput::make('name')
                    ->label(__('filament.resources.level.fields.name'))
                    ->required()
                    ->maxLength(255)
                    ->placeholder(__('filament.resources.level.placeholders.name')),

                Forms\Components\TextInput::make('order')
                    ->label(__('filament.resources.level.fields.order'))
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->placeholder(__('filament.resources.level.placeholders.order')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label(__('filament.resources.level.fields.key'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.resources.level.fields.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order')
                    ->label(__('filament.resources.level.fields.order'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.resources.level.fields.created_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('filament.resources.level.fields.updated_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('recent')
                    ->label(__('filament.resources.level.filters.recent'))
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
            'index' => Pages\ListLevels::route('/'),
            'create' => Pages\CreateLevel::route('/create'),
            'edit' => Pages\EditLevel::route('/{record}/edit'),
        ];
    }
}
