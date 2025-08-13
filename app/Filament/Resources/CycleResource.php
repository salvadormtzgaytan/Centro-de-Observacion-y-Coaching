<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CycleResource\Pages;
use App\Models\Cycle;
use App\Models\Division;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CycleResource extends Resource
{
    protected static ?string $model = Cycle::class;

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Ciclos';

    protected static ?string $modelLabel = 'Ciclo';

    protected static ?string $pluralModelLabel = 'Ciclos';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('key')
                ->label('Clave')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255)
                ->placeholder('FY2025-Q1'),

            TextInput::make('label')
                ->label('Etiqueta')
                ->required()
                ->maxLength(255)
                ->placeholder('Q1 2025'),

            TextInput::make('fiscal_year')
                ->label('Año Fiscal')
                ->required()
                ->numeric()
                ->minValue(2020)
                ->maxValue(2030),

            TextInput::make('quarter')
                ->label('Trimestre')
                ->required()
                ->numeric()
                ->minValue(1)
                ->maxValue(4),

            DatePicker::make('starts_at')
                ->label('Fecha de Inicio')
                ->required(),

            DatePicker::make('ends_at')
                ->label('Fecha de Fin')
                ->required()
                ->after('starts_at'),

            Toggle::make('is_open')
                ->label('Activo')
                ->default(true),

            Select::make('division_id')
                ->label('División')
                ->options(Division::pluck('name', 'id'))
                ->searchable()
                ->placeholder('Global (todas las divisiones)'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Clave')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('label')
                    ->label('Etiqueta')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fiscal_year')
                    ->label('Año Fiscal')
                    ->sortable(),

                TextColumn::make('quarter')
                    ->label('Q')
                    ->sortable(),

                TextColumn::make('starts_at')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable(),

                IconColumn::make('is_open')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('division.name')
                    ->label('División')
                    ->placeholder('Global')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('fiscal_year', 'desc')
            ->filters([
                SelectFilter::make('fiscal_year')
                    ->label('Año Fiscal')
                    ->options(fn () => Cycle::distinct()->pluck('fiscal_year', 'fiscal_year')),

                SelectFilter::make('is_open')
                    ->label('Estado')
                    ->options([
                        1 => 'Activo',
                        0 => 'Inactivo',
                    ]),

                SelectFilter::make('division_id')
                    ->label('División')
                    ->options(fn () => Division::pluck('name', 'id')),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCycles::route('/'),
            'create' => Pages\CreateCycle::route('/create'),
            'edit' => Pages\EditCycle::route('/{record}/edit'),
        ];
    }
}