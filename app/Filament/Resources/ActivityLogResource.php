<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationGroup = 'Auditoría';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Bitácoras';
    protected static ?string $modelLabel = 'Bitácora';
    protected static ?string $pluralModelLabel = 'Bitácoras';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('log_name')
                    ->label('Módulo')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('causer.email')
                    ->label('Hecho por')
                    ->formatStateUsing(fn($state, $record) => $record->causer?->email ?? 'N/A')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('properties')
                    ->label('Acción')
                    ->formatStateUsing(function ($state, $record) {
                        $changes = $record->changes();
                        if (!isset($changes['attributes'])) {
                            return 'Sin cambios detectados';
                        }
                        $formattedChanges = [];
                        foreach ($changes['attributes'] as $key => $newValue) {
                            if ($key === 'updated_at') {
                                continue;
                            }
                            $oldValue = $changes['old'][$key] ?? 'N/A';
                            if ($key === 'status') {
                                $formattedChanges[] = sprintf(
                                    '%s: %s → %s',
                                    'Estado',
                                    in_array($oldValue, ['draft', 'borrador']) ? 'Borrador' : 'Publicado',
                                    in_array($newValue, ['published', 'publicado']) ? 'Publicado' : 'Borrador'
                                );
                            } else {
                                $formattedChanges[] = sprintf(
                                    '%s: "%s" → "%s"',
                                    $key,
                                    static::formatChangeValue($oldValue),
                                    static::formatChangeValue($newValue)
                                );
                            }
                        }
                        return $formattedChanges ? implode('<br>', $formattedChanges) : 'Cambios no relevantes';
                    })
                    ->html()
                    ->wrap()
                    ->limit(1000)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('properties.ip')
                    ->label('IP de origen')
                    ->formatStateUsing(fn($state) => $state ?? 'N/A')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('log_name')
                    ->label('Módulo')
                    ->options(Activity::query()->distinct()->pluck('log_name', 'log_name')->toArray()),

                Tables\Filters\SelectFilter::make('causer_id')
                    ->label('Hecho por')
                    ->options(
                        \App\Models\User::query()->pluck('email', 'id')->toArray()
                    ),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected static function formatChangeValue($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}
