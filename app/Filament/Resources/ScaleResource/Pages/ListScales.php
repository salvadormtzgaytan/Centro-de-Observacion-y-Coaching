<?php

namespace App\Filament\Resources\ScaleResource\Pages;

use App\Filament\Resources\ScaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScales extends ListRecords
{
    protected static string $resource = ScaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
