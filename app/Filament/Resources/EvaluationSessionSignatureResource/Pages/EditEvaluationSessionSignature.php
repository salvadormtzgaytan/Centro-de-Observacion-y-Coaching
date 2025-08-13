<?php

namespace App\Filament\Resources\EvaluationSessionSignatureResource\Pages;

use App\Filament\Resources\EvaluationSessionSignatureResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEvaluationSessionSignature extends EditRecord
{
    protected static string $resource = EvaluationSessionSignatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
