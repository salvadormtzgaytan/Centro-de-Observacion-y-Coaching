<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use Illuminate\Database\SQLiteConnection;

class GenerateDTOsFromModels extends Command
{
    protected $signature = 'generate:dtos 
                            {--model= : Generar DTO solo para un modelo específico}
                            {--force : Sobrescribir DTOs existentes}
                            {--skip-errors : Continuar después de errores}';
    protected $description = 'Genera DTOs automáticamente para todos los modelos Eloquent';

    public function handle()
    {
        $modelName = $this->option('model');
        $models = $modelName ? [$modelName] : $this->getAllModels();

        $successCount = 0;
        $errorCount = 0;

        foreach ($models as $model) {
            try {
                if ($this->generateDTO($model)) {
                    $successCount++;
                }
            } catch (\Exception $e) {
                $errorCount++;
                $this->error("Error procesando {$model}: " . $e->getMessage());
                if (!$this->option('skip-errors')) {
                    return 1;
                }
            }
        }

        $this->info("{$successCount} DTOs generados exitosamente.");
        if ($errorCount > 0) {
            $this->warn("{$errorCount} modelos tuvieron errores.");
        }
        return 0;
    }

    protected function getAllModels()
    {
        return collect(File::allFiles(app_path('Models')))
            ->map(function ($item) {
                return 'App\\Models\\' . str($item->getFilename())->before('.php');
            })
            ->filter(function ($modelClass) {
                return class_exists($modelClass);
            })
            ->values()
            ->toArray();
    }

    protected function generateDTO($modelClass)
    {
        $reflection = new ReflectionClass($modelClass);
        $model = $reflection->newInstanceWithoutConstructor();
        
        if (!method_exists($model, 'getTable')) {
            $this->warn("El modelo {$modelClass} no es un modelo Eloquent. Omitiendo...");
            return false;
        }

        $table = $model->getTable();
        if (!Schema::hasTable($table)) {
            $this->warn("La tabla {$table} no existe. Omitiendo modelo {$modelClass}...");
            return false;
        }

        $dtoName = str($reflection->getShortName())->append('Data');
        $dtoPath = app_path("Data/{$dtoName}.php");

        if (File::exists($dtoPath) && !$this->option('force')) {
            $this->warn("DTO {$dtoName} ya existe. Usa --force para sobrescribir.");
            return false;
        }

        $columns = Schema::getColumnListing($table);
        $properties = collect($columns)
            ->reject(fn ($column) => in_array($column, ['created_at', 'updated_at', 'deleted_at']))
            ->map(function ($column) use ($table) {
                $type = $this->getColumnType($table, $column);
                $nullable = $this->isColumnNullable($table, $column) ? '?' : '';
                return "public {$nullable}{$type} \${$column}";
            })
            ->implode(",\n        ");

        File::ensureDirectoryExists(app_path('Data'));
        File::put($dtoPath, $this->buildDTOClass($dtoName, $properties));

        $this->info("DTO generado: {$dtoName}");
        return true;
    }

    protected function getColumnType($table, $column)
    {
        try {
            $type = Schema::getColumnType($table, $column);
            return $this->mapDBTypeToPHP($type);
        } catch (\Exception $e) {
            return 'mixed';
        }
    }

    protected function isColumnNullable($table, $column)
    {
        try {
            // Método alternativo para detectar si una columna es nullable
            $connection = Schema::getConnection();
            $sql = $connection->getDriverName() === 'sqlite'
                ? "PRAGMA table_info({$table})"
                : "SHOW COLUMNS FROM {$table} WHERE Field = '{$column}'";
            
            $result = $connection->select($sql);
            
            if (empty($result)) {
                return false;
            }

            if ($connection->getDriverName() === 'sqlite') {
                foreach ($result as $row) {
                    if ($row->name === $column) {
                        return !$row->notnull;
                    }
                }
            } else {
                return strtolower($result[0]->Null) === 'yes';
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function mapDBTypeToPHP($dbType)
    {
        return match(strtolower($dbType)) {
            'string', 'text', 'char', 'enum', 'varchar' => 'string',
            'integer', 'bigint', 'smallint', 'tinyint' => 'int',
            'boolean' => 'bool',
            'float', 'double', 'decimal' => 'float',
            'datetime', 'timestamp', 'date', 'datetimetz' => '\\Carbon\\Carbon',
            'json', 'array', 'simple_array' => 'array',
            default => 'mixed',
        };
    }

    protected function buildDTOClass($className, $properties)
    {
        return <<<PHP
<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Carbon\Carbon;

class {$className} extends Data
{
    public function __construct(
        {$properties}
    ) {}
    
    public static function fromModel(\$model): self
    {
        return new self(
            {$this->generateFromModelMapping($properties)}
        );
    }
}

PHP;
    }

    protected function generateFromModelMapping($properties)
    {
        return collect(explode(",\n        ", $properties))
            ->map(fn ($prop) => str($prop)->after('$')->before(' '))
            ->map(fn ($name) => "{$name}: \$model->{$name}")
            ->implode(",\n            ");
    }
}