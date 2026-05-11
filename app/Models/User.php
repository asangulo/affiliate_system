<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; // ← AGREGAR ESTE IMPORT

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles; // ← AGREGAR HasRoles AQUÍ

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'affiliate_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function toggleStatus(): void
    {
        $this->update(['is_active' => ! $this->is_active]);
    }
}