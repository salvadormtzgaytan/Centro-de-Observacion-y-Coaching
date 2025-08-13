<?php

namespace App\Filament\Resources;

use App\Filament\Exports\UserExporter;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\ViewUser;
use App\Models\User;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Gestión de Usuarios';

    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::$model::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('profile_photo_path')
                    ->label('Foto de perfil')
                    ->image()
                    ->disk('public')
                    ->directory('profile-photos')
                    ->columnSpanFull(),

                TextInput::make('name')
                    ->label(__('filament.resources.user.fields.name'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->placeholder(__('filament.resources.user.placeholders.name'))
                    ->helperText(__('filament.resources.user.helper_texts.name')),

                TextInput::make('email')
                    ->label(__('filament.resources.user.fields.email'))
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->placeholder(__('filament.resources.user.placeholders.email')),

                TextInput::make('password')
                    ->label(__('filament.resources.user.fields.password'))
                    ->password()
                    ->required(fn ($livewire) => $livewire instanceof CreateUser)
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255)
                    ->placeholder(__('filament.resources.user.placeholders.password'))
                    ->helperText(__('filament.resources.user.helper_texts.password')),

                Select::make('parent_id')
                    ->label(__('filament.resources.user.fields.parent_id'))
                    ->relationship(
                        'parent',
                        'name',
                        static function (Builder $query, $livewire) {
                            $editingId = $livewire->getRecord()?->id;
                            $query
                                ->when($editingId, fn (Builder $q) => $q->where('id', '<>', $editingId))
                                ->when($editingId, fn (Builder $q) => $q->whereNotIn(
                                    'id',
                                    User::find($editingId)?->descendants()->pluck('id')->toArray() ?? []
                                ))
                                ->orderBy('name');
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->placeholder(__('filament.resources.user.placeholders.parent')),

                Select::make('roles')
                    ->label(__('filament.resources.user.fields.roles'))
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),

                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_photo_path')
                    ->label('Foto')
                    ->disk('public')
                    ->height(40)
                    ->width(40),

                TextColumn::make('name')
                    ->label(__('filament.resources.user.fields.name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('email')
                    ->label(__('filament.resources.user.fields.email'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('parent.name')
                    ->label(__('filament.resources.user.fields.parent_id'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('roles.name')
                    ->label(__('filament.resources.user.fields.roles'))
                    ->badge(),
                TextColumn::make('last_login_at')
                    ->label('Último ingreso')
                    ->dateTime()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Activo')  // Etiqueta que se muestra en la cabecera de la columna
                    ->onColor('success')  // Color cuando está activo (verde)
                    ->offColor('danger')  // Color cuando está inactivo (rojo)
                    ->sortable()  // Permite ordenar la columna
                    ->afterStateUpdated(function (User $record, $state) {
                        $record->update(['is_active' => $state]);
                    }),
            ])
            ->filters([
                SelectFilter::make('parent_id')
                    ->label(__('filament.resources.user.filters.supervisor.label'))
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->placeholder(__('filament.resources.user.filters.supervisor.placeholder')),

                TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
                ExportBulkAction::make()
                    ->exporter(UserExporter::class)
                    ->columnMapping(false)
                    ->formats([ExportFormat::Xlsx]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
