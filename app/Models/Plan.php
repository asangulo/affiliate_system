<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['name', 'base_price', 'description', 'is_active'];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    public function affiliates(): HasMany
    {
        return $this->hasMany(Affiliate::class);
    }
}
