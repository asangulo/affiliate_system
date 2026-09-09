<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['name'];

    public function affiliates(): HasMany
    {
        return $this->hasMany(Affiliate::class);
    }
}
