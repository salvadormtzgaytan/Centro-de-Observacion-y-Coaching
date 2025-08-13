<?php

namespace App\Filament\Resources\GuideItemResponseResource\Pages;

use App\Filament\Resources\GuideItemResponseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGuideItemResponses extends ListRecords
{
    protected static string $resource = GuideItemResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
