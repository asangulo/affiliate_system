<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Agrega este trait a cualquier modelo y sus creaciones,
 * ediciones y eliminaciones quedarán registradas en activity_logs.
 *
 *   use App\Traits\LogsActivity;
 *   class Affiliate extends Model { use LogsActivity; }
 *
 * Personalizable en el modelo:
 *   protected $activityExclude = ['updated_at', 'remember_token'];
 *   public function activityLabel(): string { return $this->full_name; }
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            $model->writeActivityLog('created', [], $model->loggableAttributes($model->getAttributes()));
        });

        static::updated(function ($model) {
            $cambios = $model->getChanges();
            $viejos  = array_intersect_key($model->getOriginal(), $cambios);

            $cambios = $model->loggableAttributes($cambios);
            $viejos  = $model->loggableAttributes($viejos);

            // Si solo cambió updated_at no vale la pena registrar
            if (empty($cambios)) {
                return;
            }

            $model->writeActivityLog('updated', $viejos, $cambios);
        });

        static::deleted(function ($model) {
            $model->writeActivityLog('deleted', $model->loggableAttributes($model->getOriginal()), []);
        });
    }

    /**
     * Quita campos ruidosos o sensibles del registro.
     */
    public function loggableAttributes(array $attributes): array
    {
        $excluir = array_merge(
            ['created_at', 'updated_at', 'deleted_at', 'password', 'remember_token'],
            property_exists($this, 'activityExclude') ? $this->activityExclude : []
        );

        return array_diff_key($attributes, array_flip($excluir));
    }

    /**
     * Etiqueta legible del registro. Sobreescribible en cada modelo.
     */
    public function activityLabel(): string
    {
        return $this->full_name
            ?? $this->name
            ?? (class_basename($this) . ' #' . $this->getKey());
    }

    protected function writeActivityLog(string $evento, array $viejos, array $nuevos): void
    {
        $usuario = Auth::user();
        $etiqueta = $this->activityLabel();

        $nombreModelo = match (static::class) {
            'App\Models\Affiliate'   => 'el afiliado',
            'App\Models\Beneficiary' => 'el beneficiario',
            'App\Models\Payment'     => 'el pago',
            'App\Models\Branch'      => 'la sede',
            'App\Models\Plan'        => 'el plan',
            'App\Models\User'        => 'el usuario',
            default                  => strtolower(class_basename(static::class)),
        };

        $verbo = match ($evento) {
            'created' => 'Creó',
            'updated' => 'Editó',
            'deleted' => 'Eliminó',
            default   => ucfirst($evento),
        };

        ActivityLog::create([
            'user_id'       => $usuario?->id,
            'user_name'     => $usuario?->name ?? 'Sistema',
            'event'         => $evento,
            'subject_type'  => static::class,
            'subject_id'    => $this->getKey(),
            'subject_label' => $etiqueta,
            'description'   => "{$verbo} {$nombreModelo} {$etiqueta}",
            'old_values'    => $viejos ?: null,
            'new_values'    => $nuevos ?: null,
            'ip_address'    => Request::ip(),
        ]);
    }

    /**
     * Historial de este registro en concreto.
     */
    public function activityLogs()
    {
        return ActivityLog::where('subject_type', static::class)
                          ->where('subject_id', $this->getKey())
                          ->latest();
    }
}
