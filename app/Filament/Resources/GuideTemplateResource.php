<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuideTemplateResource\Pages;
use App\Filament\Resources\GuideTemplateResource\RelationManagers\SectionsRelationManager;
use App\Models\Channel;
use App\Models\GuideTemplate;
use App\Models\Level;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GuideTemplateResource extends Resource
{
    protected static ?string $model = GuideTemplate::class;

    protected static ?string $navigationGroup = 'Gestión de Guías';

    protected static ?int $navigationSort = 50;

    protected static ?string $navigationLabel = 'Guías';

    protected static ?string $modelLabel = 'Guía';

    protected static ?string $pluralModelLabel = 'Guías';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule, $get) {
                    $channelId = $get('channel_id');
                    if ($channelId) {
                        $rule->where('channel_id', $channelId);
                    }

                    return $rule;
                }),

            Select::make('level_id')
                ->label(__('filament.resources.guide_template.fields.level_id'))
                ->options(Level::pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required(),

            Select::make('channel_id')
                ->label(__('filament.resources.guide_template.fields.channel_id'))
                ->options(Channel::pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required(),

            Select::make('status')
                ->label(__('filament.resources.guide_template.fields.status'))
                ->options(GuideTemplate::statusLabels())
                ->native(false)
                ->required()
                ->default('draft'),

            // Repeater::make('sections')
            //     ->label(__('filament.resources.guide_template.fields.sections'))
            //     ->relationship('sections')
            //     ->orderColumn('order')
            //     ->reorderable()
            //     ->collapsible()
            //     ->columnSpanFull()
            //     ->addActionLabel(__('filament.actions.add_section'))
            //     ->minItems(1, __('filament.validation.at_least_one_section'))
            //     ->schema([
            //         TextInput::make('title')
            //             ->label(__('filament.resources.guide_template.fields.section_title'))
            //             ->required()
            //             ->maxLength(255),

            //         TextInput::make('order')
            //             ->label(__('filament.resources.guide_template.fields.section_order'))
            //             ->numeric()
            //             ->default(0)
            //             ->required(),
            //     ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament.resources.guide_template.fields.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('level.name')
                    ->label(__('filament.resources.guide_template.fields.level_id'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('channel.name')
                    ->label(__('filament.resources.guide_template.fields.channel_id'))
                    ->searchable()
                    ->sortable(),

                // contador de secciones (sin BadgeColumn)
                TextColumn::make('sections_count')
                    ->label(__('filament.resources.guide_template.fields.sections') ?: 'Secciones')
                    ->counts('sections')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('filament.resources.guide_template.fields.status'))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'draft' => 'warning',
                        'published' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => GuideTemplate::statusLabels()[$state] ?? $state)
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('filament.resources.guide_template.fields.created_at'))
                    ->dateTime()
                    ->since() // "hace X"
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label(__('filament.resources.guide_template.fields.updated_at'))
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('deleted_at')
                    ->label(__('filament.resources.guide_template.fields.deleted_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                TrashedFilter::make(),

                SelectFilter::make('status')
                    ->label(__('filament.resources.guide_template.fields.status'))
                    ->options(GuideTemplate::statusLabels()),

                SelectFilter::make('channel_id')
                    ->label(__('filament.resources.guide_template.fields.channel_id'))
                    ->options(fn () => Channel::query()->orderBy('order')->pluck('name', 'id')->all()),

                SelectFilter::make('level_id')
                    ->label(__('filament.resources.guide_template.fields.level_id'))
                    ->options(fn () => Level::query()->orderBy('order')->pluck('name', 'id')->all()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
        return [
            // Si más adelante quieres administrar grupos (many-to-many):
            // GuideGroupsRelationManager::class,
            // Y/o un RelationManager de secciones si prefieres no usar Repeater:
            SectionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuideTemplates::route('/'),
            'create' => Pages\CreateGuideTemplate::route('/create'),
            'view' => Pages\ViewGuideTemplate::route('/{record}'),
            'edit' => Pages\EditGuideTemplate::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('sections')
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
