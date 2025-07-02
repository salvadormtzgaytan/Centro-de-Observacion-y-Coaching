<?php

namespace App\Filament\Resources\ScaleResource\Pages;

use App\Filament\Resources\ScaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScale extends EditRecord
{
    protected static string $resource = ScaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
