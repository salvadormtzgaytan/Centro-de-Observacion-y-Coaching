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
use App\Models\Scale;
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
                    ->label(__('template_item.fields.template_section_id'))
                    ->relationship('section', 'title')
                    ->required(),
                Forms\Components\TextInput::make('label')
                    ->label(__('template_item.fields.label'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->label('Tipo de respuesta')
                    ->options([
                        'text'   => 'Texto libre',
                        'select' => 'Selección',
                    ])
                    ->reactive()
                    ->required()
                    ->default('select'),
                // CheckboxList para opciones de escala
                Forms\Components\CheckboxList::make('options')
                    ->label(__('template_item.fields.options'))
                    ->options(
                        fn() =>
                        Scale::all()->mapWithKeys(
                            fn($s) => [
                                $s->value => "{$s->label} ({$s->value})"
                            ]
                        )->toArray()
                    )
                    ->default(fn() => Scale::all()->pluck('value')->map(fn($v) => (string) $v)->toArray())
                    ->visible(fn(callable $get) => $get('type') === 'select')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        // $state contiene los valores seleccionados
                        $labels = Scale::whereIn('value', $state)->get()->map(fn($s) => "{$s->label} = {$s->value}")->implode(', ');
                        $set('help_text', $labels);
                    }),
                Forms\Components\Textarea::make('help_text')
                    ->label(__('template_item.fields.help_text'))
                    ->nullable()
                    ->visible(fn($get) => $get('type') === 'select')
                    ->default(
                        fn() => Scale::all()
                            ->map(fn($s) => "{$s->label} = {$s->value}")
                            ->implode(', ')
                    ),
                Forms\Components\TextInput::make('score')
                    ->label(__('template_item.fields.score'))
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('order')
                    ->label(__('template_item.fields.order'))
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('section.title')
                    ->label(__('template_item.fields.template_section_id'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('label')
                    ->label(__('template_item.fields.label'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('template_item.fields.type'))
                    ->formatStateUsing(fn($state) => $state === 'select' ? 'Selección' : ($state === 'text' ? 'Texto libre' : $state))
                    ->badge()
                    ->colors([
                        'primary' => 'select',
                        'secondary' => 'text',
                    ]),
                Tables\Columns\TextColumn::make('score')
                    ->label(__('template_item.fields.score'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order')
                    ->label(__('template_item.fields.order'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('template_item.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('template_item.fields.updated_at'))
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
