<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EvaluationSessionSignatureResource\Pages;
use App\Models\EvaluationSessionSignature;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EvaluationSessionSignatureResource extends Resource
{
    protected static ?string $model = EvaluationSessionSignature::class;
    protected static ?string $navigationGroup = 'Gestión de Evaluaciones';
    protected static ?int $navigationSort = 30;
    protected static ?string $navigationLabel = 'Firmas de Sesiones';
    protected static ?string $modelLabel = 'Firma de Sesión';
    protected static ?string $pluralModelLabel = 'Firmas de Sesiones';
    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

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

                TextColumn::make('user.name')
                    ->label('Firmante')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('signer_role')
                    ->label('Rol')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'coach' => 'success',
                        'coachee' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'coach' => 'Coach',
                        'coachee' => 'Participante',
                        default => ucfirst($state),
                    }),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'signed' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'signed' => 'Firmado',
                        'pending' => 'Pendiente',
                        'rejected' => 'Rechazado',
                        default => ucfirst($state),
                    }),

                TextColumn::make('method')
                    ->label('Método')
                    ->badge()
                    ->color('info'),

                TextColumn::make('signed_at')
                    ->label('Fecha de Firma')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                ImageColumn::make('digital_signature')
                    ->label('Firma Digital')
                    ->disk('public')
                    ->height(40)
                    ->toggleable(),

                TextColumn::make('rejection_reason')
                    ->label('Motivo de Rechazo')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'signed' => 'Firmado',
                        'pending' => 'Pendiente',
                        'rejected' => 'Rechazado',
                    ]),

                SelectFilter::make('signer_role')
                    ->label('Rol del Firmante')
                    ->options([
                        'coach' => 'Coach',
                        'coachee' => 'Participante',
                    ]),

                SelectFilter::make('method')
                    ->label('Método')
                    ->options(fn () => EvaluationSessionSignature::distinct()->pluck('method', 'method')->filter()),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['session.evaluator', 'session.participant', 'user']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvaluationSessionSignatures::route('/'),
            'create' => Pages\CreateEvaluationSessionSignature::route('/create'),
            'view' => Pages\ViewEvaluationSessionSignature::route('/{record}'),
            'edit' => Pages\EditEvaluationSessionSignature::route('/{record}/edit'),
        ];
    }


}