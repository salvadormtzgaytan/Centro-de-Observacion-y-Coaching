<?php

namespace App\Filament\Resources\EvaluationSessionResource\Pages;

use App\Filament\Resources\EvaluationSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;

class ViewEvaluationSession extends ViewRecord
{
    protected static string $resource = EvaluationSessionResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Información General')
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),
                        
                        TextEntry::make('evaluator.name')
                            ->label('Coach/Evaluador'),
                        
                        TextEntry::make('participant.name')
                            ->label('Participante'),
                        
                        TextEntry::make('division.name')
                            ->label('División'),
                        
                        TextEntry::make('date')
                            ->label('Fecha')
                            ->date('d/m/Y'),
                        
                        TextEntry::make('cycle_label')
                            ->label('Ciclo'),
                        
                        TextEntry::make('status')
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
                            ->formatStateUsing(fn (string $state) => \App\Models\EvaluationSession::labelForStatus($state)),
                    ])
                    ->columns(2),

                Section::make('Métricas de Evaluación')
                    ->schema([
                        TextEntry::make('total_score')
                            ->label('Puntaje Total')
                            ->numeric(2),
                        
                        TextEntry::make('max_score')
                            ->label('Puntaje Máximo')
                            ->numeric(2),
                        
                        TextEntry::make('overall_avg_pct')
                            ->label('Promedio General')
                            ->suffix('%')
                            ->numeric(2),
                        
                        TextEntry::make('answered_avg_pct')
                            ->label('Promedio de Respondidas')
                            ->suffix('%')
                            ->numeric(2),
                        
                        TextEntry::make('guide_responses_count')
                            ->label('Número de Guías')
                            ->badge()
                            ->color('info'),
                        
                        TextEntry::make('progress_percent')
                            ->label('Progreso')
                            ->suffix('%')
                            ->badge()
                            ->color('success'),
                    ])
                    ->columns(3),

                Section::make('Comentarios')
                    ->schema([
                        TextEntry::make('comments')
                            ->label('Comentarios')
                            ->placeholder('Sin comentarios')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => !empty($record->comments)),

                Section::make('Información del Sistema')
                    ->schema([
                        TextEntry::make('pdf_path')
                            ->label('Ruta del PDF')
                            ->placeholder('No generado'),
                        
                        TextEntry::make('created_at')
                            ->label('Creada')
                            ->dateTime('d/m/Y H:i'),
                        
                        TextEntry::make('updated_at')
                            ->label('Actualizada')
                            ->dateTime('d/m/Y H:i'),
                        
                        TextEntry::make('deleted_at')
                            ->label('Eliminada')
                            ->dateTime('d/m/Y H:i')
                            ->visible(fn ($record) => $record->deleted_at),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(false), // Deshabilitado para solo lectura
        ];
    }
}