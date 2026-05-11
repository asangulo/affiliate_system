<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Affiliate extends Model
{
    protected $fillable = [
        'contract_number',
        'full_name',
        'id_number',
        'birth_date',
        'branch_id',
        'plan_id',
        'fee_amount',
        'email',
        'phone',
        'address',
        'neighborhood',
        'advisor_name',
        'affiliation_date',
        'is_active',
    ];

    protected $casts = [
        'branch_id'        => 'integer',
        'plan_id'          => 'integer',
        'fee_amount'       => 'decimal:2',
        'affiliation_date' => 'date',
        'birth_date'       => 'date',
        'is_active'        => 'boolean',
    ];

    // ── Relaciones ────────────────────────────────────────────

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function beneficiaries()
    {
        return $this->hasMany(Beneficiary::class);
    }

    public function activeBeneficiaries()
    {
        return $this->hasMany(Beneficiary::class)->where('is_active', true);
    }

    // Usuario vinculado (rol cliente)
    public function user()
    {
        return $this->hasOne(User::class);
    }

    // ── Scopes ────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
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
}