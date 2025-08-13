<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuideResponseResource\Pages;
use App\Models\GuideResponse;
use App\Models\EvaluationSession;
use App\Models\GuideTemplate;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GuideResponseResource extends Resource
{
    protected static ?string $model = GuideResponse::class;
    protected static ?string $navigationGroup = 'Gestión de Evaluaciones';
    protected static ?int $navigationSort = 20;
    protected static ?string $navigationLabel = 'Respuestas de Guías';
    protected static ?string $modelLabel = 'Respuesta de Guía';
    protected static ?string $pluralModelLabel = 'Respuestas de Guías';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('session_id')
                ->label('Sesión de Evaluación')
                ->options(EvaluationSession::with(['evaluator', 'participant'])
                    ->get()
                    ->mapWithKeys(fn ($session) => [
                        $session->id => "#{$session->id} - {$session->evaluator->name} → {$session->participant->name}"
                    ]))
                ->searchable()
                ->required(),

            Select::make('guide_template_id')
                ->label('Plantilla de Guía')
                ->options(GuideTemplate::pluck('name', 'id'))
                ->searchable()
                ->required(),

            TextInput::make('total_score')
                ->label('Puntaje Total')
                ->numeric()
                ->step(0.01)
                ->default(0.00)
                ->disabled()
                ->helperText('Se calcula automáticamente'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('session.id')
                    ->label('Sesión')
                    ->sortable(),

                TextColumn::make('session.evaluator.name')
                    ->label('Evaluador')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('session.participant.name')
                    ->label('Participante')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('guideTemplate.name')
                    ->label('Plantilla')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_score')
                    ->label('Puntaje')
                    ->numeric(2)
                    ->sortable(),

                TextColumn::make('item_responses_count')
                    ->label('Respuestas')
                    ->counts('itemResponses')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('session.status')
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
                    ->formatStateUsing(fn (string $state) => EvaluationSession::labelForStatus($state)),

                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->since()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Actualizada')
                    ->dateTime('d/m/Y H:i')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make(),

                SelectFilter::make('session.status')
                    ->label('Estado de Sesión')
                    ->options(EvaluationSession::statusLabels()),

                SelectFilter::make('guide_template_id')
                    ->label('Plantilla')
                    ->options(fn () => GuideTemplate::pluck('name', 'id')),
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
            ->withCount('itemResponses')
            ->with(['session.evaluator', 'session.participant', 'guideTemplate'])
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuideResponses::route('/'),
            'create' => Pages\CreateGuideResponse::route('/create'),
            'view' => Pages\ViewGuideResponse::route('/{record}'),
            'edit' => Pages\EditGuideResponse::route('/{record}/edit'),
        ];
    }
}