<?php

namespace App\Filament\Imports;

use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserImporter extends Importer
{
    protected static ?string $model = User::class;

    /**
     * Define las columnas que se pueden importar.
     */
    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('email')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255']),
            ImportColumn::make('is_active')
                ->boolean()
                ->rules(['nullable', 'boolean']), // Opcional, predeterminado a true
            ImportColumn::make('password')
                ->rules(['nullable', 'max:255']), // Contraseña opcional
            ImportColumn::make('parent')
                ->relationship(),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Select::make('role')
                ->label('Rol')
                ->options(Role::pluck('name', 'id')->toArray())
                ->required()
                ->helperText('Selecciona el rol que se asignará a los usuarios importados.'),
            Checkbox::make('notify')
                ->label('Notificar a los usuarios'),
        ];
    }

    /**
     * Busca o crea un registro de usuario.
     */
    public function resolveRecord(): ?User
    {
        return User::firstOrNew([
            'email' => $this->data['email'],
        ]);
    }

    /**
     * Modifica el registro antes de crearlo.
     */
    public function mutateBeforeCreate(User $record): void
    {
        // Generar una contraseña segura si no se proporciona
        $password = $this->data['password'] ?? Str::random(16);

        // Asignar la contraseña al registro
        $record->password = $password;

        // Asignar otros campos
        $record->name = $this->data['name'];
        $record->is_active = $this->data['is_active'] ?? true; // Predeterminado a true si no se proporciona
        $record->parent_id = $this->data['parent'] ?? null;
    }

    /**
     * Modifica el registro después de crearlo.
     */
    public function mutateAfterCreate(User $record): void
    {
        // Asignar el rol seleccionado al usuario
        $roleId = $this->options['role'] ?? null;

        if ($roleId) {
            $role = Role::find($roleId);
            if ($role) {
                $record->syncRoles([$role->name]);
            }
        }

        // Enviar notificación si se seleccionó "notify"
        if ($this->options['notify'] ?? false) {
            $record->notify(new \App\Notifications\SendUserCredentials($record->email, $record->password));
        }
    }

    /**
     * Define el cuerpo de la notificación al completar la importación.
     */
    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'La importación de usuarios se ha completado con éxito. Se importaron '.number_format($import->successful_rows).' '.str('fila')->plural($import->successful_rows).'.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('fila')->plural($failedRowsCount).' no se pudieron importar.';
        }

        return $body;
    }
}
