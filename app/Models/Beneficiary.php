<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beneficiary extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'affiliate_id',
        'full_name',
        'relationship',
        'age',
        'auxilio',
        'holder_note',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }
}
