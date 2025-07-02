<?php

namespace App\Filament\Resources\TemplateItemResource\Pages;

use App\Filament\Resources\TemplateItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTemplateItems extends ListRecords
{
    protected static string $resource = TemplateItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
