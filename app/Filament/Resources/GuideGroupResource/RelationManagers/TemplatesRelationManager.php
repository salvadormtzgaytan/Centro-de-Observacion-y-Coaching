<?php

namespace App\Filament\Resources\GuideGroupResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TemplatesRelationManager extends RelationManager
{
    /**
     * Nombre EXACTO de la relación en el modelo GuideGroup.
     */
    protected static string $relationship = 'guideTemplates';

    public function form(Form $form): Form
    {
        // Si llegaras a usar Edit en pivote, aquí defines campos del pivote.
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name') // para que Filament sepa qué mostrar como título
            ->columns([
                TextColumn::make('name')
                    ->label('Template')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Estatus')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'draft' => 'warning',
                        'archived' => 'gray',
                        default => 'success',
                    }),
            ])
            ->filters([
                // tus filtros si los necesitas
            ])
            ->headerActions([
                AttachAction::make()
                    // precarga el select (evita que se vea vacío hasta teclear)
                    ->preloadRecordSelect()
                    // columnas donde buscar al teclear:
                    ->recordSelectSearchColumns(['name']),
            ])
            ->actions([
                DetachAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make(),
            ]);
    }
}
