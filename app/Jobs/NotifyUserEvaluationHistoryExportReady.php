<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\UserEvaluationHistoryExportReady;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Notifica al usuario cuando su exportación de historial está lista.
 */
class NotifyUserEvaluationHistoryExportReady implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Opcional: envía este job a una cola dedicada */
    public string $queue = 'exports';

    /** Número de intentos antes de fallar definitivamente */
    public int $tries = 5;

    /**
     * Backoff progresivo entre reintentos (segundos)
     * Espera por si el archivo aún no está materializado cuando corre el job.
     */
    public function backoff(): array
    {
        return [10, 30, 60, 120, 300];
    }

    protected User $user;
    protected string $path;         // e.g. "exports/history/123/file.xlsx"
    protected string $disk;         // e.g. "public", "s3"
    protected ?string $downloadName;

    /**
     * @param User        $user         Usuario a notificar
     * @param string      $path         Ruta relativa en el disco
     * @param string      $disk         Disco de Storage (por defecto "public")
     * @param string|null $downloadName Nombre sugerido para la descarga
     */
    public function __construct(User $user, string $path, string $disk = 'public', ?string $downloadName = null)
    {
        $this->user         = $user;
        $this->path         = $path;
        $this->disk         = $disk;
        $this->downloadName = $downloadName ?? basename($path);
    }

    public function handle(): void
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($this->disk);

        if (! $disk->exists($this->path)) {
            throw new \RuntimeException("Export file not ready: {$this->disk}:{$this->path}");
        }

        $expiresAt = now()->addDays((int) config('coaching.sign_link_ttl_days', 7));

        if (in_array($this->disk, ['s3', 'minio'], true) && method_exists($disk, 'temporaryUrl')) {
            $url = $disk->temporaryUrl($this->path, $expiresAt);
        } elseif (method_exists($disk, 'url')) {
            $url = $disk->url($this->path);
        } else {
            $url = asset('storage/' . ltrim($this->path, '/'));
        }

        $this->user->notify(new UserEvaluationHistoryExportReady($url, $this->downloadName));
    }
}
