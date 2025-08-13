<?php

namespace App\Filament\Resources\GuideItemResponseResource\Pages;

use App\Filament\Resources\GuideItemResponseResource;
use App\Models\TemplateItem;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateGuideItemResponse extends CreateRecord
{
    protected static string $resource = GuideItemResponseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $item = TemplateItem::find($data['template_item_id']);
        
        if ($item) {
            // Consolidar respuesta desde los campos específicos
            switch ($item->type) {
                case 'text':
                    if (isset($data['answer'])) {
                        $data['answer'] = [['value' => $data['answer']]];
                    }
                    break;
                    
                case 'select':
                    if (isset($data['answer_select'])) {
                        $data['answer'] = [['value' => $data['answer_select']]];
                        $data['score_obtained'] = (float) $data['answer_select'];
                    }
                    unset($data['answer_select']);
                    break;
                    
                case 'radio':
                    if (isset($data['answer_radio'])) {
                        $data['answer'] = [['value' => $data['answer_radio']]];
                        $data['score_obtained'] = (float) $data['answer_radio'];
                    }
                    unset($data['answer_radio']);
                    break;
                    
                case 'scale':
                    if (isset($data['answer_scale'])) {
                        $data['answer'] = [['value' => $data['answer_scale']]];
                        $data['score_obtained'] = (float) $data['answer_scale'];
                    }
                    unset($data['answer_scale']);
                    break;
            }
        }
        
        return $data;
    }
}
