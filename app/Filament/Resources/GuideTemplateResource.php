<?php

namespace App\Filament\Resources;

use App\Models\Scale;
use App\Filament\Resources\GuideTemplateResource\Pages;
use App\Models\GuideTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\CheckboxList;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class GuideTemplateResource extends Resource
{
    protected static ?string $navigationGroup = 'Gestión de Guías';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $navigationLabel = 'Guías';
    protected static ?string $modelLabel      = 'Guía';
    protected static ?string $pluralModelLabel= 'Guías';

    protected static ?string $model = GuideTemplate::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Detalles')
                        ->schema([
                            TextInput::make('name')
                                ->label('Nombre de la plantilla')
                                ->required()
                                ->maxLength(255),

                            Select::make('division_id')
                                ->label('División')
                                ->relationship('division', 'name')
                                ->required(),

                            Select::make('level_id')
                                ->label('Nivel')
                                ->relationship('level', 'name')
                                ->required(),

                            Select::make('channel_id')
                                ->label('Canal')
                                ->relationship('channel', 'name')
                                ->required(),

                            Select::make('status')
                                ->label('Estado')
                                ->options([
                                    'draft'     => 'Borrador',
                                    'published' => 'Publicado',
                                ])
                                ->required(),
                        ]),

                    Step::make('Secciones & Ítems')
                        ->schema([
                            Repeater::make('sections')
                                ->label('Secciones')
                                ->relationship('sections')
                                ->orderColumn('order')
                                ->reorderable()
                                ->collapsible()
                                ->addActionLabel('Añadir sección')
                                ->schema([
                                    TextInput::make('title')
                                        ->label('Título de sección')
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('order')
                                        ->label('Orden')
                                        ->numeric()
                                        ->default(0),

                                    Repeater::make('items')
                                        ->label('Ítems')
                                        ->relationship('items')
                                        ->orderColumn('order')
                                        ->reorderable()
                                        ->collapsible()
                                        ->addActionLabel('Añadir ítem')
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
                                                ->required()
                                                ->default('select'),

                                            CheckboxList::make('options')
                                                ->label('Opciones de escala')
                                                ->options(fn () => Scale::all()->pluck('label', 'value')->toArray())
                                                ->default(fn () => Scale::all()->pluck('value')->map(fn($v) => (string) $v)->toArray())
                                                ->visible(fn (callable $get) => $get('type') === 'select'),

                                            Textarea::make('help_text')
                                                ->label('Texto de ayuda')
                                                ->nullable()
                                                ->visible(fn ($get) => $get('type') === 'select')
                                                ->default(fn () => 
                                                    Scale::all()
                                                         ->map(fn($s) => "{$s->label} = {$s->value}")
                                                         ->implode(', ')
                                                ),

                                            TextInput::make('order')
                                                ->label('Orden del ítem')
                                                ->numeric()
                                                ->default(0),
                                        ]),
                                ]),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Plantilla')->searchable(),
                TextColumn::make('division.name')->label('División')->sortable(),
                TextColumn::make('level.name')->label('Nivel')->sortable(),
                TextColumn::make('channel.name')->label('Canal')->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->sortable()
                    ->badge()
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                    ])
                    ->formatStateUsing(fn (string $state): string => [
                        'draft'     => 'Borrador',
                        'published' => 'Publicado',
                    ][$state] ?? $state),
                TextColumn::make('created_at')->label('Creado')->dateTime()->sortable()->toggleable(),
                TextColumn::make('updated_at')->label('Modificado')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGuideTemplates::route('/'),
            'create' => Pages\CreateGuideTemplate::route('/create'),
            'edit'   => Pages\EditGuideTemplate::route('/{record}/edit'),
        ];
    }
}
