<?php

namespace App\Filament\Resources\TemplateItemResource\Pages;

use App\Filament\Resources\TemplateItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTemplateItem extends EditRecord
{
    protected static string $resource = TemplateItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
