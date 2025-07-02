<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\TemplateItem;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TemplateItemResource\Pages;
use App\Filament\Resources\TemplateItemResource\RelationManagers;

class TemplateItemResource extends Resource
{
    // — Menú en español dentro de "Gestión de Guías" —
    protected static ?string $navigationGroup       = 'Gestión de Guías';
    protected static ?int    $navigationSort        = 2;
    protected static ?string $navigationLabel       = 'Ítems';
    protected static ?string $modelLabel            = 'Ítem';
    protected static ?string $pluralModelLabel      = 'Ítems';

    protected static ?string $model          = TemplateItem::class;
    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('template_section_id')
                    ->label('Sección')
                    ->relationship('section', 'title')
                    ->required(),
                Forms\Components\TextInput::make('label')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('type')
                    ->required(),
                Forms\Components\Textarea::make('help_text')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('options'),
                Forms\Components\TextInput::make('score')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('template_section_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('label')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('score')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
            'index' => Pages\ListTemplateItems::route('/'),
            'create' => Pages\CreateTemplateItem::route('/create'),
            'edit' => Pages\EditTemplateItem::route('/{record}/edit'),
        ];
    }
}
