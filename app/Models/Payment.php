<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'affiliate_id',
        'period_year',
        'period_month',
        'amount',
        'paid_at',
        'method',
        'reference',
        'notes',
        'registered_by',
    ];

    protected $casts = [
        'paid_at'      => 'date',
        'amount'       => 'decimal:2',
        'period_year'  => 'integer',
        'period_month' => 'integer',
    ];

    protected $appends = ['period_label'];

    // ── Relaciones ────────────────────────────────────────────
    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    // ── Accessors ─────────────────────────────────────────────
    public function getPeriodLabelAttribute(): string
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        return ($meses[$this->period_month] ?? '?') . ' ' . $this->period_year;
    }

    // ── Scopes ────────────────────────────────────────────────
    public function scopeForPeriod($query, int $year, int $month)
    {
        return $query->where('period_year', $year)
                     ->where('period_month', $month);
    }

    public function scopeInYear($query, int $year)
    {
        return $query->where('period_year', $year);
    }

    // ── Historial ─────────────────────────────────────────────
    public function activityLabel(): string
    {
        $nombre = $this->affiliate?->full_name ?? 'afiliado #' . $this->affiliate_id;
        return "Pago de {$nombre} — {$this->period_label}";
    }
}
