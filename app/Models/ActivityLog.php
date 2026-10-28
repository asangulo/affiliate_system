<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'event',
        'subject_type',
        'subject_id',
        'subject_label',
        'description',
        'old_values',
        'new_values',
        'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    protected $appends = ['subject_name', 'event_label'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Nombre corto del modelo afectado: "Afiliado", "Pago", "Sede"...
     */
    public function getSubjectNameAttribute(): string
    {
        $map = [
            'App\Models\Affiliate'   => 'Afiliado',
            'App\Models\Beneficiary' => 'Beneficiario',
            'App\Models\Payment'     => 'Pago',
            'App\Models\Branch'      => 'Sede',
            'App\Models\Plan'        => 'Plan',
            'App\Models\User'        => 'Usuario',
            'App\Models\HolderChange' => 'Cambio de titular',
        ];

        return $map[$this->subject_type] ?? class_basename($this->subject_type ?? '');
    }

    public function getEventLabelAttribute(): string
    {
        return match ($this->event) {
            'created'  => 'Creación',
            'updated'  => 'Edición',
            'deleted'  => 'Eliminación',
            'restored' => 'Restauración',
            'login'    => 'Inicio de sesión',
            'logout'   => 'Cierre de sesión',
            'import'   => 'Importación',
            'export'   => 'Exportación',
            default    => ucfirst($this->event),
        };
    }

    /**
     * Diferencias entre valores viejos y nuevos, listas para mostrar.
     */
    public function getChangesListAttribute(): array
    {
        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];
        $out = [];

        foreach ($new as $campo => $valorNuevo) {
            $valorViejo = $old[$campo] ?? null;
            if ($valorViejo == $valorNuevo) {
                continue;
            }
            $out[] = [
                'field' => self::humanField($campo),
                'from'  => self::humanValue($campo, $valorViejo),
                'to'    => self::humanValue($campo, $valorNuevo),
            ];
        }

        return $out;
    }

    private static function humanValue(string $campo, mixed $valor): string
    {
        if ($valor === null || $valor === '') {
            return '—';
        }

        if ($campo === 'is_active') {
            return $valor ? 'Sí' : 'No';
        }

        return (string) $valor;
    }

    public static function humanField(string $campo): string
    {
        $map = [
            'full_name'        => 'Nombre',
            'id_number'        => 'Cédula',
            'contract_number'  => 'Contrato',
            'branch_id'        => 'Sede',
            'plan_id'          => 'Plan',
            'fee_amount'       => 'Valor cuota',
            'phone'            => 'Teléfono',
            'email'            => 'Correo',
            'address'          => 'Dirección',
            'neighborhood'     => 'Barrio',
            'advisor_name'     => 'Asesor',
            'affiliation_date' => 'Fecha de afiliación',
            'birth_date'       => 'Fecha de nacimiento',
            'is_active'        => 'Activo',
            'relationship'     => 'Parentesco',
            'age'              => 'Edad',
            'auxilio'          => 'Auxilio',
            'holder_note'      => 'Nota',
            'amount'           => 'Monto',
            'paid_at'          => 'Fecha de pago',
            'method'           => 'Método',
            'reference'        => 'Referencia',
            'period_year'      => 'Año',
            'period_month'     => 'Mes',
            'city'             => 'Ciudad',
            'department'       => 'Departamento',
            'manager_name'     => 'Encargado',
            'code'             => 'Código',
            'name'             => 'Nombre',
            'affiliate_id'        => 'Afiliado',
            'reason'              => 'Motivo',
            'deceased_at'         => 'Fecha de fallecimiento',
            'notes'               => 'Observaciones',
            'previous_full_name'  => 'Titular anterior',
            'previous_id_number'  => 'Cédula del titular anterior',
            'previous_birth_date' => 'Nacimiento del titular anterior',
            'previous_phone'      => 'Teléfono del titular anterior',
            'previous_email'      => 'Correo del titular anterior',
            'beneficiary_id'      => 'Beneficiario',
            'new_full_name'       => 'Nuevo titular',
            'new_id_number'       => 'Cédula del nuevo titular',
            'performed_by'        => 'Registrado por',
        ];

        return $map[$campo] ?? ucfirst(str_replace('_', ' ', $campo));
    }
}
