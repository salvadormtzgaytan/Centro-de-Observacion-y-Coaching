<?php

namespace App\Filament\Resources;


use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use App\Filament\Exports\UserExporter;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\UserResource\Pages;
use Filament\Actions\Exports\Enums\ExportFormat;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;


class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Usuarios';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Usuario';
    protected static ?string $pluralModelLabel = 'Usuarios';
    protected static ?string $navigationGroup = 'Gestión de Usuarios';
    /**
     * Show badge with total user count
     */
    public static function getNavigationBadge(): ?string
    {
        return static::$model::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ingrese el nombre completo del usuario')
                    ->helperText('El nombre completo del usuario, por ejemplo')
                    ->columnSpanFull(),

                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->placeholder('Ingrese el correo electrónico del usuario'),

                TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->required(fn($livewire) => $livewire instanceof Pages\CreateUser)
                    ->maxLength(255)
                    ->placeholder('Ingrese una contraseña segura')
                    ->dehydrated(fn($livewire) => $livewire instanceof Pages\CreateUser)
                    ->dehydrateStateUsing(fn($state) => bcrypt($state)),


                Select::make('parent_id')
                    ->label('Supervisor')
                    ->relationship(
                        'parent',
                        'name',
                        function (\Illuminate\Database\Eloquent\Builder $query, EditUser  $livewire) {
                            $editingId = $livewire->getRecord() ? $livewire->getRecord()->id : null;
                            $query
                                ->when(
                                    $editingId,
                                    fn($q)            // If there is a record being edited
                                    => $q->where('id', '<>', $editingId)
                                )
                                // Excluir a todos sus descendientes para evitar ciclos
                                ->when($editingId, fn($q) => $q->whereNotIn('id', User::find($editingId)->descendants()->pluck('id')))
                                ->orderBy('name');
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->placeholder('Seleccione un supervisor')
                    ->nullable(),

                Select::make('roles')
                    ->label('Roles')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->placeholder('Seleccione los roles del usuario')
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Correo')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('parent.name')
                    ->label('Supervisor')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge(),
            ])
            ->filters([
                //
                // Puedes agregar filtros personalizados aquí si es necesario
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Supervisor')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->placeholder('Seleccione un supervisor'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                ExportBulkAction::make()
                    ->exporter(UserExporter::class)
                    ->columnMapping(false)->formats([
                        ExportFormat::Xlsx,
                    ])
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
