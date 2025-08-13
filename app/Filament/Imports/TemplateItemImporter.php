<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\GuideTemplate;
use App\Models\TemplateItem;
use App\Models\TemplateSection;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Importer de TemplateItem para Filament 3.
 *
 * Columnas esperadas (cabeceras del archivo):
 * - guide     : Nombre de la Guía (GuideTemplate.name)
 * - section   : Título de la Sección (TemplateSection.title)
 * - question  : HTML/Texto de la pregunta
 * - type      : enum('text','select','radio','scale')
 * - order     : entero (opcional, default 0)
 *
 * El Observer de TemplateItem se encarga de asignar options/help_text
 * automáticamente cuando type ∈ {select, radio, scale}.
 */
class TemplateItemImporter extends Importer
{
    /**
     * Modelo base (para metadatos; no usamos fill automático).
     *
     * @var class-string<TemplateItem>|null
     */
    protected static ?string $model = TemplateItem::class;

    /**
     * Define las columnas mapeables desde el archivo.
     */
    public static function getColumns(): array
    {
        return [
            ImportColumn::make('guide')
                ->label('Guía (nombre)')
                ->requiredMapping()
                ->rules(['required', 'string']),

            ImportColumn::make('section')
                ->label('Sección (título)')
                ->requiredMapping()
                ->rules(['required', 'string']),

            ImportColumn::make('question')
                ->label('Pregunta')
                ->requiredMapping()
                ->rules(['required', 'string']),

            ImportColumn::make('type')
                ->label('Tipo (text|select|radio|scale)')
                ->requiredMapping()
                ->rules([
                    'required',
                    'string',
                    Rule::in(['text', 'select', 'radio', 'scale']),
                ]),

            ImportColumn::make('order')
                ->label('Orden')
                ->rules(['nullable', 'integer', 'min:0']),
        ];
    }

    /**
     * Procesa cada fila ya validada.
     * Crea o actualiza por (section_id + question "normalizada").
     */
    public function handleRecord(array $data): void
    {
        // 1) Resolver Guía por nombre
        $guide = GuideTemplate::query()->where('name', $data['guide'])->first();

        if (! $guide) {
            throw new RowImportFailedException("La guía '{$data['guide']}' no existe."); // fila fallida
        }

        // 2) Resolver Sección por título dentro de esa Guía
        $section = TemplateSection::query()
            ->where('guide_template_id', $guide->id)
            ->where('title', $data['section'])
            ->first();

        if (! $section) {
            throw new RowImportFailedException("La sección '{$data['section']}' no pertenece a la guía '{$guide->name}'.");
        }

        // 3) Normalizar "question" para identificar duplicados dentro de la misma sección
        $questionHtml = (string) $data['question'];
        $questionKey = $this->normalizeQuestionKey($questionHtml);

        // 4) Buscar existente (misma sección + misma clave normalizada)
        $existing = TemplateItem::query()
            ->where('template_section_id', $section->id)
            // Heurística: compara versión "plana" (sin tags/espacios dobles) para evitar duplicados obvios
            ->get()
            ->first(function (TemplateItem $item) use ($questionKey) {
                return $this->normalizeQuestionKey((string) $item->question) === $questionKey;
            });

        $payload = [
            'template_section_id' => $section->id,
            'question' => $questionHtml,
            'type' => $data['type'],
            'order' => isset($data['order']) ? (int) $data['order'] : 0,
        ];

        if ($existing) {
            $existing->fill($payload)->save();   // cuenta como éxito
        } else {
            TemplateItem::create($payload);      // éxito
        }
    }

    /**
     * Mensaje de notificación al finalizar el import.
     */
    public static function getCompletedNotificationBody(Import $import): string
    {
        $total = $result->totalRows;
        $ok = $result->successfulRows;
        $fail = $result->getFailedRowsCount();

        $body = "Se procesaron {$total} fila(s).\n";
        $body .= "✅ Éxitos: {$ok}\n";
        $body .= "❌ Fallos: {$fail}";

        return $body;
    }

    /**
     * Normaliza el HTML de la pregunta para comparar duplicados por contenido "plano".
     */
    private function normalizeQuestionKey(string $html): string
    {
        // Quitar tags básicos y normalizar espacios
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($html)) ?? '');
        // Lowercase para evitar diferencias de mayúsculas
        $text = Str::of($text)->lower()->value();

        return $text;
    }
}
