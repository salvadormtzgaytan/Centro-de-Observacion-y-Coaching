<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuideGroupResource\Pages;
use App\Filament\Resources\GuideGroupResource\RelationManagers\TemplatesRelationManager;
use App\Models\GuideGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GuideGroupResource extends Resource
{
    protected static ?string $model = GuideGroup::class;

    protected static ?string $navigationGroup = 'Gestión de Guías';

    protected static ?int $navigationSort = 60;

    protected static ?string $navigationLabel = 'Grupos';

    protected static ?string $modelLabel = 'Grupo';

    protected static ?string $pluralModelLabel = 'Grupos';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('filament.resources.guide_group.fields.name'))
                    ->required()
                    ->maxLength(255)
                    ->placeholder(__('filament.resources.guide_group.placeholders.name')),

                Forms\Components\Textarea::make('description')
                    ->label(__('filament.resources.guide_group.fields.description'))
                    ->maxLength(255)
                    ->placeholder(__('filament.resources.guide_group.placeholders.description')),

                Forms\Components\Toggle::make('active')
                    ->label(__('filament.resources.guide_group.fields.active'))
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.resources.guide_group.fields.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('filament.resources.guide_group.fields.description'))
                    ->searchable(),

                Tables\Columns\IconColumn::make('active')
                    ->label(__('filament.resources.guide_group.fields.active'))
                    ->boolean(),
                TextColumn::make('templates_count')
                    ->label('Plantillas')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.resources.guide_group.fields.created_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('filament.resources.guide_group.fields.updated_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label(__('filament.resources.guide_group.fields.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('active')
                    ->label(__('filament.resources.guide_group.filters.active'))
                    ->query(fn ($query) => $query->where('active', true)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Relación con plantillas de guía
            TemplatesRelationManager::class,
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->withCount('templates');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuideGroups::route('/'),
            'create' => Pages\CreateGuideGroup::route('/create'),
            'view' => Pages\ViewGuideGroup::route('/{record}'),
            'edit' => Pages\EditGuideGroup::route('/{record}/edit'),
        ];
    }
}
