<?php

namespace App\Filament\Resources\GuideTemplateResource\Pages;

use App\Filament\Resources\GuideTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGuideTemplate extends EditRecord
{
    protected static string $resource = GuideTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
