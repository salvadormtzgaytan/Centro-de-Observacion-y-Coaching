<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EvaluationSessionResource\Pages;
use App\Models\Division;
use App\Models\EvaluationSession;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EvaluationSessionResource extends Resource
{
    protected static ?string $model = EvaluationSession::class;

    protected static ?string $navigationGroup = 'Gestión de Evaluaciones';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Sesiones de Evaluación';

    protected static ?string $modelLabel = 'Sesión de Evaluación';

    protected static ?string $pluralModelLabel = 'Sesiones de Evaluación';

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('evaluator.name')
                    ->label('Coach/Evaluador')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('participant.name')
                    ->label('Participante')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('division.name')
                    ->label('División')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('cycle_label')
                    ->label('Ciclo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
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
                    ->formatStateUsing(fn (string $state) => EvaluationSession::labelForStatus($state))
                    ->sortable(),

                TextColumn::make('total_score')
                    ->label('Puntaje Total')
                    ->numeric(2)
                    ->sortable(),

                TextColumn::make('overall_avg_pct')
                    ->label('Promedio General (%)')
                    ->numeric(2)
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('answered_avg_pct')
                    ->label('Promedio Respondidas (%)')
                    ->numeric(2)
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('guide_responses_count')
                    ->label('Guías')
                    ->counts('guideResponses')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('comments')
                    ->label('Comentarios')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

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

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(EvaluationSession::statusLabels()),

                SelectFilter::make('evaluator_id')
                    ->label('Coach/Evaluador')
                    ->options(fn () => User::pluck('name', 'id')),

                SelectFilter::make('division_id')
                    ->label('División')
                    ->options(fn () => Division::pluck('name', 'id')),

                SelectFilter::make('cycle')
                    ->label('Ciclo')
                    ->options(fn () => EvaluationSession::distinct()->pluck('cycle', 'cycle')->filter()),
            ])
            ->actions([
                ViewAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    ExportBulkAction::make(),
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                ExportAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvaluationSessions::route('/'),
            'create' => Pages\CreateEvaluationSession::route('/create'),
            'view' => Pages\ViewEvaluationSession::route('/{record}'),
            'edit' => Pages\EditEvaluationSession::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('guideResponses')
            ->with(['evaluator', 'participant', 'division'])
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }


}
