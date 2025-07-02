<?php

namespace App\Filament\Exports;

use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class UserExporter extends Exporter
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('Nombre'),

            ExportColumn::make('email')
                ->label('Correo electrónico'),

            ExportColumn::make('parent.name')
                ->label('Supervisor'),

            ExportColumn::make('roles.name')
                ->label('Roles'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $rows = number_format($export->successful_rows);
        $body = "La exportación de usuarios se ha completado con {$rows} " . str('fila')->plural($export->successful_rows) . ".";

        if ($failed = $export->getFailedRowsCount()) {
            $body .= " {$failed} " . str('fila')->plural($failed) . " no se pudieron exportar.";
        }

        return $body;
    }
}
// This exporter allows exporting user data with their names, emails, supervisors, and roles.
// It uses Filament's Exporter class to define the columns and customize the completion notification.