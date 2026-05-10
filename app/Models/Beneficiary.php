<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Beneficiary extends Model
{
    protected $fillable = [
        'affiliate_id',
        'full_name',
        'relationship',
        'age',
        'auxilio',
        'holder_note',
    ];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }
}
