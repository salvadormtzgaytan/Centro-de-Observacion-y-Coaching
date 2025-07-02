<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use Filament\Actions\ExportAction;
use App\Filament\Exports\UserExporter;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Exports\Enums\ExportFormat;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Botón de "Crear usuario"
            Actions\CreateAction::make(),

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
