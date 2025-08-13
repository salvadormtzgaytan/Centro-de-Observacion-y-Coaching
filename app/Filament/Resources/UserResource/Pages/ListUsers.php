<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Exports\UserExporter;
use App\Filament\Imports\UserImporter;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Botón de "Crear usuario"
            Actions\CreateAction::make(),

            ImportAction::make()
                ->importer(UserImporter::class)
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->label('Importar usuarios')
                ->modalHeading('Importar usuarios')->options([
                    'notify' => true,
                ]),

            // Botón de "Exportar todos"
            ExportAction::make('exportAll')
                ->label('Exportar todos')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->modal(false)
                ->exporter(UserExporter::class)
                ->columnMapping(false)
                ->formats([
                    ExportFormat::Xlsx,
                ]),
        ];
    }
}
