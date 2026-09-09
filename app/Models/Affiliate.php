<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Affiliate extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'id_number',
        'contract_number',
        'full_name',
        'branch_id',
        'plan_id',
        'fee_amount',
        'phone',
        'email',
        'address',
        'neighborhood',
        'advisor_name',
        'affiliation_date',
        'birth_date',
        'is_active',
    ];

    protected $casts = [
        'affiliation_date' => 'date',
        'birth_date'       => 'date',
        'fee_amount'       => 'decimal:2',
        'is_active'        => 'boolean',
    ];

    // ── Relaciones ────────────────────────────────────────────
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function beneficiaries(): HasMany
    {
        return $this->hasMany(Beneficiary::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)
                    ->orderByDesc('period_year')
                    ->orderByDesc('period_month');
    }

    /**
     * Usuario del portal ligado a este afiliado (rol cliente).
     * La FK vive en users.affiliate_id.
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    // ── Lógica de cartera ─────────────────────────────────────

    /**
     * Mes desde el que se le empieza a cobrar.
     */
    public function billingStart(): Carbon
    {
        return ($this->affiliation_date
            ? Carbon::parse($this->affiliation_date)
            : Carbon::parse($this->created_at))->startOfMonth();
    }

    /**
     * Cuántas cuotas mensuales debería haber pagado hasta hoy.
     */
    public function expectedInstallments(?Carbon $hasta = null): int
    {
        $hasta = ($hasta ?? Carbon::now())->copy()->startOfMonth();
        $desde = $this->billingStart();

        if ($desde->greaterThan($hasta)) {
            return 0;
        }

        return $desde->diffInMonths($hasta) + 1;
    }

    /**
     * Cuántas ha pagado realmente.
     */
    public function paidInstallments(): int
    {
        return $this->payments()->count();
    }

    /**
     * Cuotas pendientes (si es 0 está al día).
     */
    public function overdueInstallments(?Carbon $hasta = null): int
    {
        return max(0, $this->expectedInstallments($hasta) - $this->paidInstallments());
    }

    /**
     * Valor total adeudado.
     */
    public function overdueAmount(?Carbon $hasta = null): float
    {
        return round($this->overdueInstallments($hasta) * (float) $this->fee_amount, 2);
    }

    public function isUpToDate(?Carbon $hasta = null): bool
    {
        return $this->overdueInstallments($hasta) === 0;
    }

    /**
     * Primer periodo sin pagar, empezando desde la afiliación.
     * Devuelve ['year' => 2026, 'month' => 3, 'label' => 'Marzo 2026'] o null si está al día.
     */
    public function nextDuePeriod(): ?array
    {
        $pagados = $this->payments()
                        ->get()
                        ->map(fn ($p) => $p->period_year . '-' . str_pad($p->period_month, 2, '0', STR_PAD_LEFT))
                        ->flip();

        $cursor = $this->billingStart();
        $limite = Carbon::now()->startOfMonth()->addMonths(1);

        while ($cursor->lessThanOrEqualTo($limite)) {
            $clave = $cursor->year . '-' . str_pad($cursor->month, 2, '0', STR_PAD_LEFT);

            if (!$pagados->has($clave)) {
                return [
                    'year'  => $cursor->year,
                    'month' => $cursor->month,
                    'label' => self::monthName($cursor->month) . ' ' . $cursor->year,
                    'date'  => $cursor->copy()->toDateString(),
                ];
            }

            $cursor->addMonth();
        }

        return null;
    }

    /**
     * Resumen de cartera listo para enviar al frontend.
     */
    public function paymentSummary(): array
    {
        $esperadas = $this->expectedInstallments();
        $pagadas   = $this->paidInstallments();
        $mora      = max(0, $esperadas - $pagadas);
        $ultimo    = $this->payments()->first();

        return [
            'expected'      => $esperadas,
            'paid'          => $pagadas,
            'overdue'       => $mora,
            'overdue_amount'=> round($mora * (float) $this->fee_amount, 2),
            'up_to_date'    => $mora === 0,
            'last_payment'  => $ultimo ? [
                'period'  => $ultimo->period_label,
                'paid_at' => optional($ultimo->paid_at)->toDateString(),
                'amount'  => (float) $ultimo->amount,
            ] : null,
            'next_due'      => $this->nextDuePeriod(),
        ];
    }

    public static function monthName(int $m): string
    {
        return [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ][$m] ?? '?';
    }

    // ── Scopes ────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    // ── Helpers ───────────────────────────────────────────────
    public function toggleStatus(): void
    {
        $this->update(['is_active' => ! $this->is_active]);
    }

    public function activityLabel(): string
    {
        return $this->full_name ?? ('afiliado ' . $this->id_number);
    }
}
