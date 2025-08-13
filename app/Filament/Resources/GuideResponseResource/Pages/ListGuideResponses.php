<?php

namespace App\Filament\Resources\GuideResponseResource\Pages;

use App\Filament\Resources\GuideResponseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGuideResponses extends ListRecords
{
    protected static string $resource = GuideResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
