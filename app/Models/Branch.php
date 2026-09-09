<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'city',
        'department',
        'address',
        'phone',
        'email',
        'manager_name',
        'opened_at',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'opened_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function affiliates(): HasMany
    {
        return $this->hasMany(Affiliate::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function activityLabel(): string
    {
        return $this->name;
    }
}
