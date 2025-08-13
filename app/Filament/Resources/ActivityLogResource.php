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
    protected static ?int $navigationSort = 70;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('log_name')
                    ->label(__('activity_log.fields.log_name'))
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('activity_log.fields.description'))
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('causer.email')
                    ->label(__('activity_log.fields.causer'))
                    ->formatStateUsing(fn($state, $record) => $record->causer?->email ?? __('activity_log.empty'))
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('properties')
                    ->label(__('activity_log.fields.action'))
                    ->formatStateUsing(function ($state, $record) {
                        $changes = $record->changes();
                        if (!isset($changes['attributes'])) {
                            return __('activity_log.no_changes');
                        }
                        $formattedChanges = [];
                        foreach ($changes['attributes'] as $key => $newValue) {
                            if ($key === 'updated_at') {
                                continue;
                            }
                            $oldValue = $changes['old'][$key] ?? __('activity_log.empty');
                            if ($key === 'status') {
                                $formattedChanges[] = sprintf(
                                    '%s: %s → %s',
                                    __('activity_log.fields.status'),
                                    in_array($oldValue, ['draft', 'borrador']) ? __('activity_log.status.draft') : __('activity_log.status.published'),
                                    in_array($newValue, ['published', 'publicado']) ? __('activity_log.status.published') : __('activity_log.status.draft')
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
                        return $formattedChanges ? implode('<br>', $formattedChanges) : __('activity_log.no_relevant_changes');
                    })
                    ->html()
                    ->wrap()
                    ->limit(1000)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('properties.ip')
                    ->label(__('activity_log.fields.ip'))
                    ->formatStateUsing(fn($state) => $state ?? __('activity_log.empty'))
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('activity_log.fields.created_at'))
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('log_name')
                    ->label(__('activity_log.fields.log_name'))
                    ->options(Activity::query()->distinct()->pluck('log_name', 'log_name')->toArray()),

                Tables\Filters\SelectFilter::make('causer_id')
                    ->label(__('activity_log.fields.causer'))
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
