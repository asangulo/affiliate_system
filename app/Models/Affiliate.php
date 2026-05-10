<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Affiliate extends Model
{
    protected $fillable = [
        'contract_number',
        'full_name',
        'id_number',
        'branch_id',
        'plan_id',
        'fee_amount',
        'phone',
        'email',
        'affiliation_date',
    ];

    protected $casts = [
        'branch_id' => 'integer',
        'plan_id' => 'integer',
        'fee_amount' => 'decimal:2',
        'affiliation_date' => 'date',
    ];

    public function branch() {
        return $this->belongsTo(Branch::class);
    }

    public function plan() {
        return $this->belongsTo(Plan::class);
    }

    public function beneficiaries() {
        return $this->hasMany(Beneficiary::class);
    }
}
