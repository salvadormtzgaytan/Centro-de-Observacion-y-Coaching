<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuideItemResponseResource\Pages;
use App\Models\GuideItemResponse;
use App\Models\GuideResponse;
use App\Models\Scale;
use App\Models\TemplateItem;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GuideItemResponseResource extends Resource
{
    protected static ?string $model = GuideItemResponse::class;

    protected static ?string $navigationGroup = 'Gestión de Evaluaciones';

    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel = 'Respuestas de Ítems';

    protected static ?string $modelLabel = 'Respuesta de Ítem';

    protected static ?string $pluralModelLabel = 'Respuestas de Ítems';

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('guide_response_id')
                ->label('Respuesta de Guía')
                ->options(GuideResponse::with(['session.evaluator', 'session.participant', 'guideTemplate'])
                    ->get()
                    ->filter(fn ($gr) => $gr->session && $gr->session->evaluator && $gr->session->participant && $gr->guideTemplate)
                    ->mapWithKeys(fn ($gr) => [
                        $gr->id => "#{$gr->session->id} - {$gr->guideTemplate->name} ({$gr->session->evaluator->name} → {$gr->session->participant->name})",
                    ]))
                ->searchable()
                ->required(),

            Select::make('template_item_id')
                ->label('Ítem de Plantilla')
                ->options(TemplateItem::with('section')
                    ->get()
                    ->filter(fn ($item) => $item->section)
                    ->mapWithKeys(fn ($item) => [
                        $item->id => "{$item->section->title} - {$item->question}",
                    ]))
                ->searchable()
                ->required()
                ->live(),

            TextInput::make('answer')
                ->label('Respuesta')
                ->visible(function (Get $get) {
                    $itemId = $get('template_item_id');
                    if (! $itemId) {
                        return false;
                    }
                    $item = TemplateItem::find($itemId);

                    return $item && $item->type === 'text';
                })
                ->required(function (Get $get) {
                    $itemId = $get('template_item_id');
                    if (! $itemId) {
                        return false;
                    }
                    $item = TemplateItem::find($itemId);

                    return $item && $item->type === 'text';
                }),

            Select::make('answer_select')
                ->label('Seleccionar Opción')
                ->options(function (Get $get) {
                    $itemId = $get('template_item_id');
                    if (! $itemId) {
                        return [];
                    }
                    $item = TemplateItem::find($itemId);
                    if (! $item || $item->type !== 'select') {
                        return [];
                    }

                    return Scale::orderBy('order')
                        ->get()
                        ->mapWithKeys(fn ($scale) => [$scale->value => "{$scale->label} ({$scale->value})"]);
                })
                ->live()
                ->afterStateUpdated(fn ($state, $set) => $set('score_obtained', $state))
                ->visible(function (Get $get) {
                    $itemId = $get('template_item_id');
                    if (! $itemId) {
                        return false;
                    }
                    $item = TemplateItem::find($itemId);

                    return $item && $item->type === 'select';
                })
                ->required(function (Get $get) {
                    $itemId = $get('template_item_id');
                    if (! $itemId) {
                        return false;
                    }
                    $item = TemplateItem::find($itemId);

                    return $item && $item->type === 'select';
                }),

            Radio::make('answer_radio')
                ->label('Seleccionar Opción')
                ->options(function (Get $get) {
                    $itemId = $get('template_item_id');
                    if (! $itemId) {
                        return [];
                    }
                    $item = TemplateItem::find($itemId);
                    if (! $item || $item->type !== 'radio') {
                        return [];
                    }

                    return Scale::orderBy('order')
                        ->get()
                        ->mapWithKeys(fn ($scale) => [$scale->value => "{$scale->label} ({$scale->value})"]);
                })
                ->live()
                ->afterStateUpdated(fn ($state, $set) => $set('score_obtained', $state))
                ->visible(function (Get $get) {
                    $itemId = $get('template_item_id');
                    if (! $itemId) {
                        return false;
                    }
                    $item = TemplateItem::find($itemId);

                    return $item && $item->type === 'radio';
                })
                ->required(function (Get $get) {
                    $itemId = $get('template_item_id');
                    if (! $itemId) {
                        return false;
                    }
                    $item = TemplateItem::find($itemId);

                    return $item && $item->type === 'radio';
                }),

            Select::make('answer_scale')
                ->label('Escala de Valoración')
                ->options(function (Get $get) {
                    $itemId = $get('template_item_id');
                    if (! $itemId) {
                        return [];
                    }
                    $item = TemplateItem::find($itemId);
                    if (! $item || $item->type !== 'scale') {
                        return [];
                    }

                    return Scale::orderBy('order')
                        ->get()
                        ->mapWithKeys(fn ($scale) => [$scale->value => "{$scale->label} ({$scale->value})"]);
                })
                ->live()
                ->afterStateUpdated(fn ($state, $set) => $set('score_obtained', $state))
                ->visible(function (Get $get) {
                    $itemId = $get('template_item_id');
                    if (! $itemId) {
                        return false;
                    }
                    $item = TemplateItem::find($itemId);

                    return $item && $item->type === 'scale';
                })
                ->required(function (Get $get) {
                    $itemId = $get('template_item_id');
                    if (! $itemId) {
                        return false;
                    }
                    $item = TemplateItem::find($itemId);

                    return $item && $item->type === 'scale';
                })
                ->helperText(function (Get $get) {
                    $itemId = $get('template_item_id');
                    if (! $itemId) {
                        return null;
                    }
                    $item = TemplateItem::find($itemId);

                    return $item && $item->help_text ? $item->help_text : null;
                }),

            TextInput::make('score_obtained')
                ->label('Puntaje Obtenido')
                ->numeric()
                ->step(0.01)
                ->default(0.00)
                ->readOnly()
                ->visible(function (Get $get) {
                    $itemId = $get('template_item_id');
                    if (! $itemId) {
                        return true;
                    }
                    $item = TemplateItem::find($itemId);

                    return $item && $item->isScorable();
                }),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('response.session.id')
                    ->label('Sesión')
                    ->sortable(),

                TextColumn::make('response.session.evaluator.name')
                    ->label('Evaluador')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('response.session.participant.name')
                    ->label('Participante')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('response.guideTemplate.name')
                    ->label('Plantilla')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('item.section.title')
                    ->label('Sección')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('item.question')
                    ->label('Pregunta')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    })
                    ->searchable(),

                TextColumn::make('item.type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'text' => 'gray',
                        'select' => 'info',
                        'radio' => 'success',
                        'scale' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('answer')
                    ->label('Respuesta')
                    ->formatStateUsing(function ($state, $record) {
                        if (empty($state)) {
                            return '—';
                        }

                        if (is_string($state)) {
                            return $state;
                        }

                        if (is_array($state)) {
                            $values = [];
                            foreach ($state as $item) {
                                if (is_array($item)) {
                                    if (isset($item['option'])) {
                                        $values[] = $item['option'];
                                    } elseif (isset($item['value'])) {
                                        $values[] = $item['value'];
                                    } else {
                                        $values[] = json_encode($item);
                                    }
                                } else {
                                    $values[] = (string) $item;
                                }
                            }

                            return implode(', ', array_filter($values));
                        }

                        return (string) $state;
                    })
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return is_string($state) && strlen($state) > 50 ? $state : null;
                    }),

                TextColumn::make('score_obtained')
                    ->label('Puntaje')
                    ->numeric(2)
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),

                TextColumn::make('response.session.status')
                    ->label('Estado Sesión')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'draft' => 'gray',
                        'in_progress' => 'warning',
                        'pending' => 'info',
                        'signed' => 'success',
                        'completed' => 'primary',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => \App\Models\EvaluationSession::labelForStatus($state)),

                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('item.type')
                    ->label('Tipo de Ítem')
                    ->options([
                        'text' => 'Texto',
                        'select' => 'Selección',
                        'radio' => 'Radio',
                        'scale' => 'Escala',
                    ]),

                SelectFilter::make('response.session.status')
                    ->label('Estado de Sesión')
                    ->options(\App\Models\EvaluationSession::statusLabels()),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'response.session.evaluator',
                'response.session.participant',
                'response.guideTemplate',
                'item.section',
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuideItemResponses::route('/'),
            'create' => Pages\CreateGuideItemResponse::route('/create'),
            'view' => Pages\ViewGuideItemResponse::route('/{record}'),
            'edit' => Pages\EditGuideItemResponse::route('/{record}/edit'),
        ];
    }
}
