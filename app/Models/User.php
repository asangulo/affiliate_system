<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

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

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    // ── Relaciones ─────────────────────────────────────────────

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    // ── Helpers ────────────────────────────────────────────────

    public function toggleStatus(): void
    {
        $this->update(['is_active' => ! $this->is_active]);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isEmpleado(): bool
    {
        return $this->hasRole('empleado');
    }

    public function isCliente(): bool
    {
        return $this->hasRole('cliente');
    }
}