<?php

namespace App\Filament\Resources;

use App\Filament\Imports\TemplateItemImporter;
use App\Filament\Resources\TemplateItemResource\Pages;
use App\Models\GuideTemplate;
use App\Models\Scale;
use App\Models\TemplateItem;
use App\Models\TemplateSection;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TemplateItemResource extends Resource
{
    protected static ?string $model = TemplateItem::class;

    protected static ?string $navigationGroup = 'Gestión de Guías';

    protected static ?int $navigationSort = 50;

    protected static ?string $navigationLabel = 'Preguntas';

    protected static ?string $modelLabel = 'Pregunta';

    protected static ?string $pluralModelLabel = 'Preguntas';

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Guía (deriva de la sección del registro al editar)
            Select::make('guide_template_id')
                ->label('Guía')
                ->options(fn () => GuideTemplate::query()->pluck('name', 'id'))
                ->searchable()
                ->required()
                ->live()
                ->afterStateUpdated(fn ($state, Set $set) => $set('template_section_id', null))
                ->afterStateHydrated(function (Set $set, ?TemplateItem $record) {
                    $set('guide_template_id', $record?->section?->guide_template_id);
                })
                ->default(fn (?TemplateItem $record) => $record?->section?->guide_template_id),

            // Sección
            Select::make('template_section_id')
                ->label('Sección')
                ->options(function (Get $get) {
                    $templateId = $get('guide_template_id');

                    return $templateId
                        ? TemplateSection::query()
                            ->where('guide_template_id', $templateId)
                            ->orderBy('order')
                            ->pluck('title', 'id')
                        : collect();
                })
                ->searchable()
                ->required(),

            // Pregunta
            RichEditor::make('question')
                ->label('Pregunta')
                ->required()
                ->toolbarButtons([
                    'bold', 'italic', 'underline', 'strike', 'link',
                    'bulletList', 'orderedList', 'blockquote', 'code', 'undo', 'redo',
                ])
                ->columnSpanFull(),

            // Tipo
            Select::make('type')
                ->label('Tipo')
                ->options([
                    'text' => 'Texto libre',
                    'select' => 'Selección',
                    'radio' => 'Opción única',
                    'scale' => 'Escala',
                ])
                ->required()
                ->default('select')
                ->live()
                ->afterStateUpdated(function (string $state, Set $set) {
                    // Sin UI de opciones: tomar SIEMPRE de Scale
                    if (in_array($state, ['select', 'radio', 'scale'], true)) {
                        $labels = Scale::query()
                            ->orderBy('order')
                            ->get()
                            ->map(fn ($s) => "{$s->label} = {$s->value}")
                            ->implode(', ');
                        $options = Scale::query()
                            ->orderBy('order')
                            ->pluck('value')
                            ->map(fn ($v) => (string) $v)
                            ->values()
                            ->toArray();

                        $set('help_text', $labels);
                        $set('options', $options);
                    } else {
                        $set('help_text', null);
                        $set('options', null);
                    }
                }),

            // Solo mostrar una pista con los valores disponibles cuando aplique
            Placeholder::make('scale_preview')
                ->label('Valores disponibles (desde Escalas)')
                ->content(function (Get $get) {
                    if (! in_array($get('type'), ['select', 'radio', 'scale'], true)) {
                        return '—';
                    }

                    return Scale::query()
                        ->orderBy('order')
                        ->get()
                        ->map(fn ($s) => "{$s->label} ({$s->value})")
                        ->implode(' · ');
                })
                ->helperText('Se cargan automáticamente desde el catálogo de Escalas. No es necesario capturarlos.'),

            // Orden
            Forms\Components\TextInput::make('order')
                ->label('Orden')
                ->numeric()
                ->default(0)
                ->required(),

            // Guardamos pero ocultamos: se calculan automáticamente
            Hidden::make('help_text'),
            Hidden::make('options'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('section.guideTemplate.name')
                    ->label('Guía')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('section.title')
                    ->label('Sección')
                    ->badge()
                    ->color(fn ($record) => ['primary', 'success', 'warning', 'danger', 'info', 'gray'][crc32($record->section->title ?? '') % 6])
                    ->sortable(),

                TextColumn::make('question')
                    ->label('Pregunta')
                    ->html()
                    ->wrap()
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state) => [
                        'select' => 'Selección',
                        'radio' => 'Opción única',
                        'scale' => 'Escala',
                        'text' => 'Texto libre',
                    ][$state] ?? $state)
                    ->badge()
                    ->colors([
                        'primary' => ['select', 'radio', 'scale'],
                        'secondary' => ['text'],
                    ])
                    ->sortable(),

                TextColumn::make('order')
                    ->label('Orden')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                ImportAction::make('importQuestions')->importer(TemplateItemImporter::class),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
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
            'index' => Pages\ListTemplateItems::route('/'),
            'create' => Pages\CreateTemplateItem::route('/create'),
            'edit' => Pages\EditTemplateItem::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScope(SoftDeletingScope::class);
    }

    /**
     * Seguridad extra en el backend: asegura persistencia coherente aunque la UI no envíe los campos.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (in_array($data['type'] ?? 'text', ['select', 'radio', 'scale'], true)) {
            $labels = Scale::query()
                ->orderBy('order')
                ->get()
                ->map(fn ($s) => "{$s->label} = {$s->value}")
                ->implode(', ');

            $data['help_text'] = $labels;
            $data['options'] = Scale::query()
                ->orderBy('order')
                ->pluck('value')
                ->map(fn ($v) => (string) $v)
                ->values()
                ->toArray();
        } else {
            $data['help_text'] = null;
            $data['options'] = null;
        }

        return $data;
    }
}
