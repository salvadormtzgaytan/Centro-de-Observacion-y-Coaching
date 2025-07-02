<?php

namespace App\Filament\Resources\GuideTemplateResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('label')
                    ->label('Pregunta')
                    ->required()
                    ->maxLength(255),

                Select::make('type')
                    ->label('Tipo de respuesta')
                    ->options([
                        'text'   => 'Texto libre',
                        'select' => 'Selección',
                    ])
                    ->reactive()
                    ->default('select')
                    ->required(),

                Select::make('options')
                    ->label('Opciones de escala')
                    ->options([
                        0.0 => 'Reforzar',
                        0.5 => 'Cumple',
                        1.0 => 'Excede',
                    ])
                    ->multiple()
                    ->visible(fn (callable $get) => $get('type') === 'select'),

                Textarea::make('help_text')
                    ->label('Texto de ayuda')
                    ->nullable()
                    ->visible(fn (callable $get) => $get('type') === 'select'),

                TextInput::make('score')
                    ->label('Puntaje')
                    ->numeric()
                    ->required()
                    ->default(0.0),

                TextInput::make('order')
                    ->label('Orden')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')->label('Pregunta')->searchable(),
                Tables\Columns\TextColumn::make('type')->label('Tipo')->sortable(),
                Tables\Columns\TextColumn::make('score')->label('Puntaje')->sortable(),
                Tables\Columns\TextColumn::make('order')->label('Orden')->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
